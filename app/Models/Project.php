<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'title',
        'digital_banking_id',
        'developer_id',
    ];

    public function progresses()
    {
        return $this->hasMany(\App\Models\Progress::class);
    }

    public function digitalBanking()
    {
        return $this->belongsTo(User::class, 'digital_banking_id');
    }
    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    // Rata-rata keinginan awal dari semua progress proyek (dibulatkan)
    public function getDesiredAveragePercentAttribute(): int
    {
        return (int) round($this->progresses()->avg('desired_percent') ?? 0);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updates()
    {
        return $this->hasMany(ProgressUpdate::class);
    }
    // app/Models/Project.php
    public function getIsFinishedAttribute(): bool
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        if ($this->progresses->isEmpty()) return false;

        foreach ($this->progresses as $pr) {
            $latest = $pr->updates->first(); // karena kita sort desc
            $real   = (int) ($latest->progress_percent ?? $latest->percent ?? 0);

            if (is_null($pr->confirmed_at) || $real < (int) $pr->desired_percent) {
                return false;
            }
        }

        return true;
    }
      public function getFinishedAtCalcAttribute()
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load('progresses');
        }

        $dates = $this->progresses
            ->pluck('confirmed_at')
            ->filter();

        return $dates->isNotEmpty() ? $dates->max() : null;
    }

}

