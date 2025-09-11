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

}

