<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupervisorNotification extends Notification
{
    use Queueable;

    // Tipe yang dipakai di view & controller
    public const PROJECT_CREATED_BY_DIG = 'project_created_by_dig';
    public const PROJECT_DONE           = 'project_done';
    public const PROJECT_UNMET          = 'project_unmet';

    public array $dataPayload;

    public function __construct(array $payload)
    {
        // Wajib ada type, project_id, project_name minimal
        $this->dataPayload = [
            'type'         => $payload['type']         ?? null,
            'project_id'   => $payload['project_id']   ?? null,
            'project_name' => $payload['project_name'] ?? null,
            'message'      => $payload['message']      ?? null,
            'when'         => $payload['when']         ?? now()->toISOString(),
        ];
    }

    public function via($notifiable)
    {
        return ['database']; // jangan ShouldQueue agar langsung tersimpan tanpa worker
    }

    public function toDatabase($notifiable)
    {
        return $this->dataPayload;
    }

    public function toArray($notifiable)
    {
        return $this->dataPayload;
    }
}
