<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Http\JsonResponse;

class ModerationController extends Controller
{
    public function pin(Thread $thread): JsonResponse
    {
        return $this->toggle($thread, 'is_pinned');
    }

    public function lock(Thread $thread): JsonResponse
    {
        return $this->toggle($thread, 'is_locked');
    }

    public function hide(Thread $thread): JsonResponse
    {
        $this->authorize('moderate', Thread::class);

        $thread->timestamps = false;
        $thread->update(['status' => $thread->status === 'hidden' ? 'published' : 'hidden']);

        return response()->json(['data' => ['status' => $thread->status]]);
    }

    private function toggle(Thread $thread, string $flag): JsonResponse
    {
        $this->authorize('moderate', Thread::class);

        $thread->timestamps = false;
        $thread->update([$flag => ! $thread->{$flag}]);

        return response()->json(['data' => [$flag => (bool) $thread->{$flag}]]);
    }
}
