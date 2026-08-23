<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Http\Requests\StoreThreadRequest;
use App\Modules\Forum\Http\Requests\UpdateThreadRequest;
use App\Modules\Forum\Http\Resources\ThreadResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ThreadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $threads = Thread::query()
            ->with(['category', 'user', 'tags'])
            ->when($request->query('category'), fn ($query, $slug) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($request->query('tag'), fn ($query, $slug) => $query->whereHas(
                'tags',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($request->query('q'), fn ($query, $term) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('content', 'like', "%{$term}%")
            ))
            ->where('status', 'published')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return ThreadResource::collection($threads);
    }

    public function show(Request $request, Thread $thread): ThreadResource
    {
        // user('api') instead of middleware so guests can still view the thread
        $user = $request->user('api');

        // hidden threads only show for moderators and their own author
        if ($thread->status !== 'published') {
            abort_unless($user && ($user->isModerator() || $user->id === $thread->user_id), 404);
        }

        $thread->load(['category', 'user', 'tags']);

        if ($user) {
            $thread->load(['votes' => fn ($q) => $q->where('user_id', $user->id)]);
            $thread->loadExists([
                'bookmarkers as is_bookmarked' => fn ($q) => $q->where('users.id', $user->id),
                'followers as is_following' => fn ($q) => $q->where('users.id', $user->id),
            ]);
        }

        // a view shouldn't bump updated_at
        $thread->timestamps = false;
        $thread->increment('views_count');
        $thread->timestamps = true;

        return new ThreadResource($thread);
    }

    public function store(StoreThreadRequest $request): ThreadResource
    {
        $data = $request->validated();

        $thread = Thread::create([
            'category_id' => $data['category_id'],
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'content' => $data['content'],
        ])->fresh();

        if (! empty($data['tags'])) {
            $thread->tags()->sync($data['tags']);
        }

        return new ThreadResource($thread->load(['category', 'user', 'tags']));
    }

    public function update(UpdateThreadRequest $request, Thread $thread): ThreadResource
    {
        $this->authorize('update', $thread);

        $thread->update($request->validated());

        if ($request->has('tags')) {
            $thread->tags()->sync($request->input('tags', []));
        }

        return new ThreadResource($thread->load(['category', 'user', 'tags']));
    }

    public function destroy(Thread $thread): \Illuminate\Http\Response
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        return response()->noContent();
    }
}
