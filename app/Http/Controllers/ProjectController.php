<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Dashboard DIG
     * - Ambil projects + PJ DIG/IT
     * - Ambil progresses + 1 update terbaru/ progress (untuk ring/preview)
     * - Kirim juga daftar user DIG & IT untuk dropdown di modal
     */
    public function index()
    {
        $projects = Project::with([
                'digitalBanking:id,name,username',
                'developer:id,name,username',
                'progresses' => function ($q) {
                    $q->with(['updates' => fn ($uq) => $uq->latest()]);
                },
            ])
            ->latest()
            ->get();

        $digitalUsers = User::where('role', 'digital_banking')
            ->orderBy('name')
            ->get(['id','name','username']);

        $itUsers = User::where('role', 'it')
            ->orderBy('name')
            ->get(['id','name','username']);

        return view('dig.dashboard', compact('projects','digitalUsers','itUsers'));
    }

    /**
     * Simpan project baru + daftar progress awal
     * Redirect ke dashboard DIG (projects.index) dengan flash success.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                         => ['required','string','max:255'],
            'description'                  => ['nullable','string'],
            'digital_banking_id'           => ['required','exists:users,id'],
            'developer_id'                 => ['required','exists:users,id'],
            'progresses'                   => ['required','array','min:1'],
            'progresses.*.name'            => ['required','string','max:255'],
            'progresses.*.start_date'      => ['required','date'],
            'progresses.*.end_date'        => ['required','date','after_or_equal:progresses.*.start_date'],
            'progresses.*.desired_percent' => ['required','integer','min:0','max:100'],
        ]);

        $project = Project::create([
            'name'               => $data['name'],
            'description'        => $data['description'] ?? null,
            'created_by'         => Auth::id(),
            'digital_banking_id' => $data['digital_banking_id'],
            'developer_id'       => $data['developer_id'],
        ]);

        foreach ($data['progresses'] as $p) {
            $project->progresses()->create([
                'name'            => $p['name'],
                'start_date'      => $p['start_date'],
                'end_date'        => $p['end_date'],
                'desired_percent' => $p['desired_percent'],
            ]);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil dibuat.');
    }

    /**
     * Tampilkan dashboard dengan panel detail project tertentu terbuka.
     * (opsional; jika kamu pakai show di rute tertentu)
     */
    public function show(Project $project)
    {
        // Detail lengkap project terpilih
        $project->load([
            'creator:id,name,username',
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => fn ($q) => $q->with(['updates' => fn ($uq) => $uq->latest()]),
        ]);

        // Daftar project untuk list kiri/atas
        $projects = Project::with([
                'digitalBanking:id,name,username',
                'developer:id,name,username',
                'progresses' => fn ($q) => $q->with(['updates' => fn ($uq) => $uq->latest()]),
            ])
            ->withCount('progresses')
            ->latest()
            ->get();

        // Dropdown modal juga disediakan
        $digitalUsers = User::where('role', 'digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role', 'it')->orderBy('name')->get(['id','name','username']);

        return view('dig.dashboard', compact('projects','project','digitalUsers','itUsers'))
            ->with('openProjectDetail', true);
    }
}
