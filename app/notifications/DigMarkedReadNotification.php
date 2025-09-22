<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DigMarkedReadNotification extends Notification
{
    use Queueable;

    public array $payload;

    /**
     * @param array $payload
     *  - project_id
     *  - project_name
     *  - by_user_id
     *  - by_name
     *  - by_role = 'digital_banking'
     *  - message
     *  - source_notification_id
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($notifiable)
    {
        return ['database']; // simpan di tabel notifications
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'         => 'dig_marked_read',
            'message'      => $this->payload['message'] ?? 'Digital Banking telah membaca notifikasi.',
            'project_id'   => $this->payload['project_id'] ?? null,
            'project_name' => $this->payload['project_name'] ?? null,

            // Siapa yang menandai baca
            'by_user_id'   => $this->payload['by_user_id'] ?? null,
            'by_name'      => $this->payload['by_name'] ?? null,
            'by_role'      => $this->payload['by_role'] ?? 'digital_banking',

            // Target role & konvensi tampilan
            'target_role'  => 'it',

            // referensi notif asal (opsional)
            'source_notification_id' => $this->payload['source_notification_id'] ?? null,
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
