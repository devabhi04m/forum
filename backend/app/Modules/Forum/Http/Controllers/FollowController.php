<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function toggle(Request $request, Thread $thread): JsonResponse
    {
        $result = $request->user()->followedThreads()->toggle($thread->id);

        return response()->json([
            'data' => ['following' => count($result['attached']) > 0],
        ]);
    }
}
