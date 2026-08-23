<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryAdminController extends Controller
{
    // unlike the public endpoint this one includes inactive categories
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount('threads')
            ->with(['children' => fn ($q) => $q->withCount('threads')])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): CategoryResource
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'integer', 'exists:forum_categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        Cache::forget('forum.categories.tree');

        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // slug stays as-is so existing links keep working
        $category->update($data);

        Cache::forget('forum.categories.tree');

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category): Response
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($category->threads()->exists(), 422, 'Move or delete its threads first.');
        abort_if($category->children()->exists(), 422, 'Delete its subcategories first.');

        $category->delete();

        Cache::forget('forum.categories.tree');

        return response()->noContent();
    }
}
