<?php

// app/Models/Notification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['target_role','data','read_at'];
    protected $casts = [
        'data'   => 'array',
        'read_at'=> 'datetime',
    ];
}
