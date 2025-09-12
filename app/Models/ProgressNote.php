<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressNote extends Model
{
    protected $fillable = [
        'progress_id','role','body','created_by'
    ];

    public function progress()
    {
        return $this->belongsTo(Progress::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
