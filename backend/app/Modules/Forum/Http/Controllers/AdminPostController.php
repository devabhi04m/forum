<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $posts = Post::query()
            ->with(['user:id,name', 'thread' => fn ($query) => $query->withTrashed()->select('id', 'title', 'slug')])
            ->when($request->query('status') === 'deleted', fn ($query) => $query->onlyTrashed())
            ->when($request->query('q'), fn ($query, $q) => $query->where('content', 'like', "%{$q}%"))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($posts->items())->map(fn (Post $post) => [
                'id' => $post->id,
                'excerpt' => Str::limit(strip_tags($post->content), 140),
                'user' => $post->user?->only(['id', 'name']),
                'thread' => $post->thread?->only(['id', 'title', 'slug']),
                'likes_count' => $post->likes_count,
                'created_at' => $post->created_at,
                'deleted_at' => $post->deleted_at,
            ]),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    // first delete is a soft delete; deleting an already-trashed post is permanent
    public function destroy(Request $request, Post $post): Response
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($post->trashed()) {
            $post->forceDelete();

            return response()->noContent();
        }

        DB::transaction(function () use ($post) {
            $thread = $post->thread()->withTrashed()->first();
            $post->delete();

            if (! $thread) {
                return;
            }

            $updates = ['replies_count' => max(0, $thread->replies_count - 1)];

            if ($thread->last_post_id === $post->id) {
                $lastPost = $thread->posts()->latest('created_at')->first();
                $updates['last_post_id'] = $lastPost?->id;
                $updates['last_post_at'] = $lastPost?->created_at;
            }

            $thread->timestamps = false;
            $thread->forceFill($updates)->save();
        });

        return response()->noContent();
    }

    public function restore(Request $request, Post $post): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($post->trashed(), 422, 'Post is not deleted.');

        DB::transaction(function () use ($post) {
            $post->restore();

            $thread = $post->thread()->withTrashed()->first();
            if (! $thread) {
                return;
            }

            $updates = ['replies_count' => $thread->replies_count + 1];

            if (! $thread->last_post_at || $post->created_at->gt($thread->last_post_at)) {
                $updates['last_post_id'] = $post->id;
                $updates['last_post_at'] = $post->created_at;
            }

            $thread->timestamps = false;
            $thread->forceFill($updates)->save();
        });

        return response()->json(['data' => ['id' => $post->id, 'deleted_at' => null]]);
    }
}
