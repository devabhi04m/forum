<?php

namespace App\Modules\Forum\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'thread_id' => $this->thread_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'is_solution' => $this->is_solution,
            'likes_count' => $this->likes_count,
            'my_vote' => $this->whenLoaded('votes', fn () => (int) ($this->votes->first()->vote ?? 0)),
            'user' => new UserSummaryResource($this->whenLoaded('user')),
            'thread' => $this->whenLoaded('thread', fn () => [
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'slug' => $this->thread->slug,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
