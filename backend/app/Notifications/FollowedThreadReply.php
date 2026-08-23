<?php

namespace App\Notifications;

use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

// new post in a thread you follow
class FollowedThreadReply extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Thread $thread,
        private Post $post,
        private string $actorName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'followed',
            'thread_slug' => $this->thread->slug,
            'thread_title' => $this->thread->title,
            'actor' => $this->actorName,
            'excerpt' => Str::limit($this->post->content, 100),
        ];
    }
}
