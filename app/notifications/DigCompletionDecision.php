<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DigCompletionDecision extends Notification
{
    use Queueable;

    public function __construct(
        public int $projectId,
        public string $projectName,
        public bool $meets,
        public ?int $byId,
        public ?string $byName
    ) {}

    public function via($notifiable): array
    {
        return ['database']; // simpan ke tabel notifications bawaan Laravel
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'          => 'dig_completion_decision',
            'project_id'    => $this->projectId,
            'project_name'  => $this->projectName,
            'decision'      => $this->meets ? 'memenuhi' : 'tidak_memenuhi',
            'status_label'  => $this->meets
                                   ? 'Project Selesai, Memenuhi'
                                   : 'Project Selesai, Tidak Memenuhi',
            'by_id'         => $this->byId,
            'by_name'       => $this->byName ?? 'Digital Banking',
            'decided_at'    => now()->toISOString(), // stempel waktu keputusan
            'message'       => $this->meets
                                   ? 'Project dinyatakan MEMENUHI oleh Digital Banking.'
                                   : 'Project dinyatakan TIDAK MEMENUHI oleh Digital Banking.',
        ];
    }
}
