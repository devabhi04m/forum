<?php

namespace App\Modules\Forum\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'status' => $this->status,
            'is_pinned' => $this->is_pinned,
            'is_locked' => $this->is_locked,
            'is_featured' => $this->is_featured,
            'views_count' => $this->views_count,
            'replies_count' => $this->replies_count,
            'likes_count' => $this->likes_count,
            'my_vote' => $this->whenLoaded('votes', fn () => (int) ($this->votes->first()->vote ?? 0)),
            'is_bookmarked' => $this->whenHas('is_bookmarked', fn ($value) => (bool) $value),
            'is_following' => $this->whenHas('is_following', fn ($value) => (bool) $value),
            'last_post_at' => $this->last_post_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => new UserSummaryResource($this->whenLoaded('user')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
