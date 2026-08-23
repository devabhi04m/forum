<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function toggle(Request $request, Thread $thread): JsonResponse
    {
        $result = $request->user()->bookmarkedThreads()->toggle($thread->id);

        return response()->json([
            'data' => ['bookmarked' => count($result['attached']) > 0],
        ]);
    }
}
