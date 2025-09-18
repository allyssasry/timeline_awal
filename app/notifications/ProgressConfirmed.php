<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProgressConfirmed extends Notification
{
    use Queueable;

    public function __construct(public $progress) {}

    public function via($notifiable)
    {
        return ['database']; // simpan ke tabel notifications
    }

    public function toDatabase($notifiable): array
    {
        $p = $this->progress;
        $project = $p->project;
        $late = false;
        if ($p->end_date) {
            $late = now()->toDateString() > $p->end_date; // contoh logika "telat"
        }

        return [
            'project_id'    => $project->id,
            'project_name'  => $project->name,
            'progress_id'   => $p->id,
            'progress_name' => $p->name,
            'message'       => 'Progress dikonfirmasi oleh IT.',
            'late'          => $late,
        ];
    }
}
