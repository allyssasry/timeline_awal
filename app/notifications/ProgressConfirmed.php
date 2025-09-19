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
        return ['database']; // langsung ke DB, tanpa queue
    }

    public function toDatabase($notifiable): array
    {
        $p       = $this->progress->loadMissing(['project.developer','project.digitalBanking','updates' => fn($q)=>$q->orderByDesc('update_date')]);
        $project = $p->project;

        $latest  = $p->updates->first();
        $latestPercent = (int) ($latest->percent ?? $latest->progress_percent ?? 0);
        $targetPercent = (int) $p->desired_percent;

        $byRole = optional($this->confirmedBy)->role;
        $message = $byRole === 'it'
            ? 'Progress telah dikonfirmasi oleh IT'
            : ($byRole === 'digital_banking' ? 'Progress telah dikonfirmasi oleh DIG' : 'Progress telah dikonfirmasi');

        return [
            // kunci yang difilter di Blade
            'type'           => 'progress_confirmed',
            'developer_role' => optional($project->developer)->role, // diharapkan 'it'
            'by_role'        => $byRole,                             // 'it' saat IT konfirmasi
            'target_role'    => 'digital_banking',

            // info untuk tampilan
            'project_id'     => $project?->id,
            'project_name'   => $project?->name,
            'progress_id'    => $p->id,
            'progress_name'  => $p->name,
            'by_user_id'     => optional($this->confirmedBy)->id,
            'by_name'        => optional($this->confirmedBy)->name,
            'message'        => $message,
            'late'           => $latestPercent < $targetPercent,
            'latest_percent' => $latestPercent,
            'target_percent' => $targetPercent,
        ];
    }
}
