<?php
// app/Policies/ProgressPolicy.php

namespace App\Policies;

use App\Models\Progress;
use App\Models\User;

class ProgressPolicy
{
   
// app/Policies/ProgressPolicy.php
public function manage(\App\Models\User $user, \App\Models\Progress $progress): bool
{
    return (int)$progress->created_by === (int)$user->id;
}
    
}
