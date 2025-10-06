<?php

namespace App\Http\Controllers\Dig;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // hanya auth standar
    }

    public function index()
    {
        if (auth()->user()?->role !== 'digital_banking') {
            abort(403, 'Halaman ini khusus Digital Banking.');
        }

        $projects = Project::with([
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => fn($q) => $q->with([
                'creator:id,name,role',
                'updates' => fn($u) => $u->latest(),
            ]),
        ])->latest()->get();

        $digitalUsers = User::where('role','digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role','it')->orderBy('name')->get(['id','name','username']);

        return view('dig.dashboard', compact('projects','digitalUsers','itUsers'));
    }
}
