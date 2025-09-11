<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model {
  protected $table = 'progress_updates';

    protected $fillable = [
        'progress_id',
        'update_date',
        'percent',
        'note',
        'updated_by',
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }
    public function creator() {
        return $this->belongsTo(User::class,'created_by');
    }
    
}
