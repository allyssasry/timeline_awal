<?php
// app/Policies/ProgressPolicy.php

namespace App\Policies;

use App\Models\Progress;
use App\Models\User;

class ProgressPolicy
{
    public function manage(User $user, Progress $progress): bool
    {
        if ($user->role === 'it') {
            return (int)$progress->created_by === (int)$user->id;
        }
        // DIG (dan role lain) bebas
        return true;
    }
}
