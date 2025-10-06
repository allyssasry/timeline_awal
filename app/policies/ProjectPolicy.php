<?php
// app/Policies/ProjectPolicy.php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;


class ProjectPolicy
{
    public function manage(User $user, Project $project)
    {
        return (int)$project->created_by === (int)$user->id;
    }
}
