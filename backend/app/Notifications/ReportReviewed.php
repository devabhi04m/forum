<?php

namespace App\Notifications;

use App\Modules\Forum\Entities\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

// a report you filed was resolved or dismissed
class ReportReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Report $report) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'report',
            'status' => $this->report->status,
            'reason' => Str::limit($this->report->reason, 100),
            'thread_slug' => $this->report->thread?->slug ?? $this->report->post?->thread?->slug,
            'thread_title' => $this->report->thread?->title ?? $this->report->post?->thread?->title,
        ];
    }
}
