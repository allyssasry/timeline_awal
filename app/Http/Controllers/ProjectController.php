<?php

namespace App\Http\Controllers;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller {
    public function index() {
        $projects = Project::with('creator')->get();
        return view('projects.index',compact('projects'));
    }

    public function create() {
        return view('projects.create');
    }

    public function store(Request $request) {
        $request->validate(['name'=>'required']);
        Project::create([
            'name'=>$request->name,
            'description'=>$request->description,
            'created_by'=>Auth::id(),
        ]);
        return redirect()->route('projects.index');
    }

    public function show(Project $project) {
        return view('projects.show',compact('project'));
    }
}
