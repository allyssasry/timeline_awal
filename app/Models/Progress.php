<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progresses'; // <-- WAJIB ditambahkan

    protected $fillable = [
        'project_id',
        'name',
        'start_date',
        'end_date',
        'desired_percent',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    // app/Models/Progress.php
    public function updates()
    {
        return $this->hasMany(\App\Models\ProgressUpdate::class)->orderByDesc('update_date');
    }
    public function notes()
    {
        return $this->hasMany(\App\Models\ProgressNote::class)->latest();
    }


    // accessor (opsional)
    public function getLatestPercentAttribute(): int
    {
        $u = $this->updates()->first();
        return $u ? (int) $u->percent : 0;
    }
}
