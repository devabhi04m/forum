<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        // cache plain arrays, models don't survive the db cache store round-trip
        $payload = Cache::remember('forum.categories.tree', 60, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('threads')
                ->with(['children' => fn ($query) => $query->where('is_active', true)->withCount('threads')])
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        });

        return response()->json(['data' => $payload]);
    }

    public function show(Category $category): CategoryResource
    {
        $category->loadCount('threads')->load(['children' => fn ($query) => $query->where('is_active', true)->withCount('threads')]);

        return new CategoryResource($category);
    }
}
