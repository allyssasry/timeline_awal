<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupervisorStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $payload // status, project info, dll
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // payload minimal:
        // - status: it_done | dig_done | project_done
        // - project_id, project_name
        // - when: now()->toISOString()
        return [
            'type'         => 'supervisor_status',
            'target_role'  => 'supervisor',
            ...$this->payload,
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
