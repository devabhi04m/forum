<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Http\Resources\PostResource;
use App\Modules\Forum\Http\Resources\ThreadResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// everything the logged-in user owns: their threads, replies and bookmarks
class ProfileController extends Controller
{
    public function threads(Request $request): AnonymousResourceCollection
    {
        $threads = $request->user()->forumThreads()
            ->with(['category', 'user', 'tags'])
            ->latest()
            ->paginate(20);

        return ThreadResource::collection($threads);
    }

    public function posts(Request $request): AnonymousResourceCollection
    {
        $posts = $request->user()->forumPosts()
            ->with(['user', 'thread:id,title,slug'])
            ->latest()
            ->paginate(20);

        return PostResource::collection($posts);
    }

    public function bookmarks(Request $request): AnonymousResourceCollection
    {
        $threads = $request->user()->bookmarkedThreads()
            ->with(['category', 'user', 'tags'])
            ->latest('forum_bookmarks.created_at')
            ->paginate(20);

        return ThreadResource::collection($threads);
    }
}
