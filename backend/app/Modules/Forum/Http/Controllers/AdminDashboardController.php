<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Report;
use App\Modules\Forum\Entities\Tag;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('access-admin-panel'), 403);

        $weekAgo = now()->subWeek();

        return response()->json([
            'data' => [
                'totals' => [
                    'users' => User::count(),
                    'threads' => Thread::count(),
                    'posts' => Post::count(),
                    'categories' => Category::count(),
                    'tags' => Tag::count(),
                    'open_reports' => Report::where('status', 'open')->count(),
                    'banned_users' => User::whereNotNull('banned_at')->count(),
                ],
                'this_week' => [
                    'users' => User::where('created_at', '>=', $weekAgo)->count(),
                    'threads' => Thread::where('created_at', '>=', $weekAgo)->count(),
                    'posts' => Post::where('created_at', '>=', $weekAgo)->count(),
                ],
                'latest_users' => User::query()
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'role', 'banned_at', 'created_at']),
                'latest_threads' => Thread::query()
                    ->with(['user:id,name', 'category:id,name,slug'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (Thread $thread) => [
                        'id' => $thread->id,
                        'title' => $thread->title,
                        'slug' => $thread->slug,
                        'status' => $thread->status,
                        'replies_count' => $thread->replies_count,
                        'user' => $thread->user?->only(['id', 'name']),
                        'category' => $thread->category?->only(['id', 'name', 'slug']),
                        'created_at' => $thread->created_at,
                    ]),
                'top_categories' => Category::query()
                    ->withCount('threads')
                    ->orderByDesc('threads_count')
                    ->limit(5)
                    ->get(['id', 'name', 'slug', 'icon']),
            ],
        ]);
    }
}
