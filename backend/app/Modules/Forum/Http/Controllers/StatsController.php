<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Report;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('moderate', Thread::class);

        $weekAgo = now()->subWeek();

        return response()->json([
            'data' => [
                'users' => User::count(),
                'threads' => Thread::count(),
                'posts' => Post::count(),
                'open_reports' => Report::where('status', 'open')->count(),
                'users_this_week' => User::where('created_at', '>=', $weekAgo)->count(),
                'threads_this_week' => Thread::where('created_at', '>=', $weekAgo)->count(),
                'posts_this_week' => Post::where('created_at', '>=', $weekAgo)->count(),
            ],
        ]);
    }
}
