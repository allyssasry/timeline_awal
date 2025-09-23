<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // ← opsional: hapus jika belum pakai queue
use Illuminate\Notifications\Notification;

class DigMarkedReadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Payload final yang akan disimpan di kolom `data` (JSON).
     *
     * @var array<string, mixed>
     */
    protected array $payload;

    /**
     * @param array{
     *   project_id?: int|string|null,
     *   project_name?: string|null,
     *   by_user_id?: int|string|null,
     *   by_name?: string|null,
     *   by_role?: 'digital_banking'|'it'|string|null,
     *   message?: string|null,
     *   source_notification_id?: string|null
     * } $payload
     */
    public function __construct(array $payload = [])
    {
        // Pastikan kunci-kunci penting selalu ada
        $this->payload = array_merge([
            'type'        => 'dig_marked_read',                  // ← difilter di IT
            'target_role' => 'it',                               // ← difilter di IT
            'message'     => 'Digital Banking telah membaca notifikasi.',
            'project_id'  => null,
            'project_name'=> null,
            'by_user_id'  => null,
            'by_name'     => null,
            'by_role'     => 'digital_banking',
            'source_notification_id' => null,
        ], $payload ?? []);
    }

    /**
     * Channel notifikasi.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke tabel `notifications`.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }

    /**
     * (Opsional) representasi array umum.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
