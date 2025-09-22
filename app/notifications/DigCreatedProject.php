<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DigCreatedProject extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public $actor) {} // $actor = user DIG pembuat

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'         => 'dig_project_created',
            'message'      => 'Digital Banking Membuat Project',
            'project_id'   => $this->project->id,
            'project_name' => $this->project->name,
            'by_role'      => 'digital_banking',
            'target_role'  => 'it',
            'by_user_id'   => $this->actor?->id,
            'by_user_name' => $this->actor?->name,
            'late'         => false,
        ];
    }
}
