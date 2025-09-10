<?php

namespace App\Http\Controllers;
use App\Models\ProgressUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller {
    public function store(Request $request, $projectId) {
        ProgressUpdate::create([
            'project_id'=>$projectId,
            'created_by'=>Auth::id(),
            'notes'=>$request->notes,
            'progress_percent'=>$request->progress_percent,
        ]);
        return back();
    }

    public function edit(ProgressUpdate $progress) {
        return view('progress.edit',compact('progress'));
    }

    public function update(Request $request, ProgressUpdate $progress) {
        $progress->update([
            'notes'=>$request->notes,
            'progress_percent'=>$request->progress_percent,
        ]);
        return back();
    }
}
