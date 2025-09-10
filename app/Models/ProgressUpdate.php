<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model {
    protected $fillable = ['project_id','created_by','notes','progress_percent'];

    public function project() {
        return $this->belongsTo(Project::class);
    }
    public function creator() {
        return $this->belongsTo(User::class,'created_by');
    }
}
