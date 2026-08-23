<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        // tags barely ever change, same plain-array caching as categories
        $payload = Cache::remember('forum.tags.all', 300, function () {
            return Tag::query()->orderBy('name')->get(['id', 'name', 'slug'])->toArray();
        });

        return response()->json(['data' => $payload]);
    }
}
