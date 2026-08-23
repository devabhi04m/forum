<?php

namespace App\Modules\Forum\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'status' => $this->status,
            'reporter' => new UserSummaryResource($this->whenLoaded('reporter')),
            'thread' => $this->whenLoaded('thread', fn () => [
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'slug' => $this->thread->slug,
            ]),
            'post' => $this->whenLoaded('post', fn () => [
                'id' => $this->post->id,
                'excerpt' => Str::limit($this->post->content, 120),
                'thread' => $this->post->thread ? [
                    'title' => $this->post->thread->title,
                    'slug' => $this->post->thread->slug,
                ] : null,
            ]),
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
        ];
    }
}
