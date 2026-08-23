<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminTagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $tags = Tag::query()
            ->withCount('threads')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $tags]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('forum_tags', 'name')],
        ]);

        $tag = Tag::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        Cache::forget('forum.tags.all');

        return response()->json(['data' => $tag], 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('forum_tags', 'name')->ignore($tag->id)],
        ]);

        $tag->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        Cache::forget('forum.tags.all');

        return response()->json(['data' => $tag]);
    }

    public function destroy(Request $request, Tag $tag): Response
    {
        abort_unless($request->user()->isAdmin(), 403);

        $tag->threads()->detach();
        $tag->delete();

        Cache::forget('forum.tags.all');

        return response()->noContent();
    }
}
