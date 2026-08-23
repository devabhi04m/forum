<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminThreadController extends Controller
{
    // every thread regardless of status, including soft-deleted ones
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-threads'), 403);

        $status = $request->query('status');

        $threads = Thread::query()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->when($status === 'deleted', fn ($query) => $query->onlyTrashed())
            ->when(in_array($status, ['published', 'hidden']), fn ($query) => $query->where('status', $status))
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($threads->items())->map(fn (Thread $thread) => [
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
                'status' => $thread->status,
                'is_pinned' => $thread->is_pinned,
                'is_locked' => $thread->is_locked,
                'replies_count' => $thread->replies_count,
                'views_count' => $thread->views_count,
                'user' => $thread->user?->only(['id', 'name']),
                'category' => $thread->category?->only(['id', 'name', 'slug']),
                'created_at' => $thread->created_at,
                'deleted_at' => $thread->deleted_at,
            ]),
            'meta' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    // first delete is a soft delete; deleting an already-trashed thread is permanent
    public function destroy(Request $request, Thread $thread): Response
    {
        abort_unless($request->user()->can('manage-threads'), 403);

        $thread->trashed() ? $thread->forceDelete() : $thread->delete();

        return response()->noContent();
    }

    public function restore(Request $request, Thread $thread): JsonResponse
    {
        abort_unless($request->user()->can('manage-threads'), 403);
        abort_unless($thread->trashed(), 422, 'Thread is not deleted.');

        $thread->restore();

        return response()->json(['data' => ['id' => $thread->id, 'deleted_at' => null]]);
    }
}
