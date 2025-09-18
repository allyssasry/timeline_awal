<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Progress;
use App\Models\User;

class ProgressConfirmed extends Notification
{
    use Queueable;

    public function __construct(
        public Progress $progress,
        public ?User $confirmedBy = null
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $p       = $this->progress;
        $project = $p->project;

        // status telat (opsional)
        $late = false;
        if ($p->end_date) {
            $late = now()->toDateString() > $p->end_date;
        }

        // ambil metadata peran
        $developerUser = optional($project)->developer;
        $digitalUser   = optional($project)->digitalBanking;

        return [
            'type'           => 'progress_confirmed',    // untuk filter jenis notifikasi
            'project_id'     => optional($project)->id,
            'project_name'   => optional($project)->name,
            'progress_id'    => $p->id,
            'progress_name'  => $p->name,

            // metadata tentang siapa yang konfirmasi & peran siapa developer project
            'by_user_id'     => optional($this->confirmedBy)->id,
            'by_role'        => optional($this->confirmedBy)->role,       // contoh: 'it'
            'developer_id'   => optional($developerUser)->id,
            'developer_role' => optional($developerUser)->role,           // contoh: 'it'
            'target_role'    => 'digital_banking',                         // penonton halaman ini

            // UI message
            'message'        => 'Progress dikonfirmasi oleh IT.',
            'late'           => $late,
        ];
    }
}
