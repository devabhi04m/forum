<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Http\Requests\StorePostRequest;
use App\Modules\Forum\Http\Resources\PostResource;
use App\Notifications\FollowedThreadReply;
use App\Notifications\MentionedInPost;
use App\Notifications\ThreadReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request, Thread $thread): AnonymousResourceCollection
    {
        $user = $request->user('api');

        $posts = $thread->posts()
            ->with('user')
            ->when($user, fn ($q) => $q->with(['votes' => fn ($v) => $v->where('user_id', $user->id)]))
            ->orderBy('created_at')
            ->paginate(20);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request, Thread $thread): PostResource
    {
        $this->authorize('reply', $thread);

        $post = DB::transaction(function () use ($request, $thread) {
            $post = $thread->posts()->create([
                'user_id' => $request->user()->id,
                'parent_id' => $request->input('parent_id'),
                'content' => $request->input('content'),
            ]);

            $thread->forceFill([
                'replies_count' => $thread->replies_count + 1,
                'last_post_id' => $post->id,
                'last_post_at' => $post->created_at,
            ])->save();

            return $post;
        });

        $this->sendReplyNotifications($thread, $post, $request->user());

        return new PostResource($post->load('user'));
    }

    private function sendReplyNotifications(Thread $thread, Post $post, User $actor): void
    {
        $notified = [$actor->id];

        // thread owner first
        $thread->loadMissing('user');
        if ($thread->user && ! in_array($thread->user_id, $notified)) {
            $thread->user->notify(new ThreadReply($thread, $post, $actor->name));
            $notified[] = $thread->user_id;
        }

        // @mentions: "@JaneDoe" matches the name "Jane Doe" with spaces stripped
        preg_match_all('/@([A-Za-z0-9_]+)/', $post->content, $matches);
        $tokens = array_values(array_unique(array_map('strtolower', $matches[1])));

        if ($tokens) {
            $placeholders = implode(',', array_fill(0, count($tokens), '?'));
            User::query()
                ->whereRaw("REPLACE(LOWER(name), ' ', '') IN ({$placeholders})", $tokens)
                ->whereNotIn('id', $notified)
                ->get()
                ->each(function (User $user) use ($thread, $post, $actor, &$notified) {
                    $user->notify(new MentionedInPost($thread, $post, $actor->name));
                    $notified[] = $user->id;
                });
        }

        // then everyone following the thread who wasn't already covered
        $thread->followers()
            ->whereNotIn('users.id', $notified)
            ->get()
            ->each(fn (User $user) => $user->notify(new FollowedThreadReply($thread, $post, $actor->name)));
    }

    public function update(StorePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);

        $post->update(['content' => $request->input('content')]);

        return new PostResource($post->load('user'));
    }

    public function destroy(Post $post): \Illuminate\Http\Response
    {
        $this->authorize('delete', $post);

        DB::transaction(function () use ($post) {
            $thread = $post->thread;
            $post->delete();

            $updates = ['replies_count' => max(0, $thread->replies_count - 1)];

            if ($thread->last_post_id === $post->id) {
                $lastPost = $thread->posts()->latest('created_at')->first();
                $updates['last_post_id'] = $lastPost?->id;
                $updates['last_post_at'] = $lastPost?->created_at;
            }

            $thread->forceFill($updates)->save();
        });

        return response()->noContent();
    }
}
