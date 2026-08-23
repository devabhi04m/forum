<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Report;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Http\Resources\ReportResource;
use App\Notifications\ReportReviewed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function thread(Request $request, Thread $thread): JsonResponse
    {
        return $this->store($request, ['thread_id' => $thread->id, 'post_id' => null]);
    }

    public function post(Request $request, Post $post): JsonResponse
    {
        return $this->store($request, ['thread_id' => null, 'post_id' => $post->id]);
    }

    private function store(Request $request, array $target): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // one open report per user per target is enough
        $report = Report::firstOrCreate(
            [...$target, 'user_id' => $request->user()->id, 'status' => 'open'],
            ['reason' => $data['reason']]
        );

        return response()->json(['data' => ['id' => $report->id, 'status' => $report->status]], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('moderate', Thread::class);

        $reports = Report::query()
            ->with(['reporter', 'thread:id,title,slug', 'post:id,thread_id,content', 'post.thread:id,title,slug'])
            ->when(
                $request->query('status'),
                fn ($q, $status) => $q->where('status', $status),
                fn ($q) => $q->where('status', 'open')
            )
            ->latest()
            ->paginate(20);

        return ReportResource::collection($reports);
    }

    public function update(Request $request, Report $report): ReportResource
    {
        $this->authorize('moderate', Thread::class);

        $data = $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
        ]);

        $report->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $report->reporter?->notify(new ReportReviewed($report));

        return new ReportResource($report->load(['reporter', 'thread:id,title,slug', 'post:id,thread_id,content', 'post.thread:id,title,slug']));
    }
}
