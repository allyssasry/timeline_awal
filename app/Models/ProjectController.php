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
    public function create()
    {
        // Dropdown penanggung jawab berdasar role
        $digitalUsers = User::where('role', 'digital_banking')->orderBy('name')->get();
        $itUsers      = User::where('role', 'it')->orderBy('name')->get();

        // Tetap render dashboard, tapi kirim flag agar modal "Tambah Project" dibuka
        $projects = Project::with(['creator', 'progresses'])->withCount('progresses')->get();

        return view('dig.dashboard', compact('projects', 'digitalUsers', 'itUsers'))
               ->with('openCreateModal', true);
    }

    public function index()
    {
        $projects = Project::with([
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => function ($q) {
                $q->with(['updates' => fn($uq) => $uq->latest() ]);
            },
        ])
        ->latest()
        ->get();

        $digitalUsers = User::where('role', 'digital_banking')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        $itUsers = User::where('role', 'it')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('dig.dashboard', compact('projects', 'digitalUsers', 'itUsers'));
    }

    /**
     * Simpan project baru + daftar progress awal
     * Hanya user dengan role digital_banking yang boleh membuat project.
     * Redirect ke dashboard DIG (projects.index) dengan flash success.
     */
    public function store(Request $request)
    {
        // === Tambahan dari snippet: batasi hanya Digital Banking ===
        if (auth()->user()?->role !== 'digital_banking') {
            abort(403, 'Hanya Digital Banking yang dapat membuat project.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'digital_banking_id' => ['required', 'exists:users,id'],
            'developer_id' => ['required', 'exists:users,id'],

            // Progresses awal
            'progresses' => ['required', 'array', 'min:1'],
            'progresses.*.name' => ['required', 'string', 'max:255'],
            'progresses.*.start_date' => ['required', 'date'],
            'progresses.*.end_date' => ['required', 'date', 'after_or_equal:progresses.*.start_date'],
            'progresses.*.desired_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Buat project
        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => Auth::id(),
            'digital_banking_id' => $data['digital_banking_id'],
            'developer_id' => $data['developer_id'],
        ]);

        // Buat daftar progress awal
        foreach ($data['progresses'] as $p) {
            $project->progresses()->create([
                'name' => $p['name'],
                'start_date' => $p['start_date'],
                'end_date' => $p['end_date'],
                'desired_percent' => $p['desired_percent'],
            ]);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil dibuat.');
    }

    /**
     * Detail project (halaman terpisah).
     */
    public function show(Project $project)
    {
        // load relasi agar detail lengkap
        $project->load(['progresses.updates', 'digitalBanking', 'developer', 'creator']);

        return view('dig.detail', compact('project'));
    }

    public function edit(Project $project)
    {
        // kalau kamu butuh daftar user DIG/IT untuk select:
        $digitalUsers = \App\Models\User::where('role', 'digital_banking')->get();
        $itUsers = \App\Models\User::where('role', 'it')->get();

        return view('projects.edit', compact('project', 'digitalUsers', 'itUsers'));
    }

    // SIMPAN PERUBAHAN
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'digital_banking_id' => ['required', 'exists:users,id'],
            'developer_id' => ['required', 'exists:users,id'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('dig.dashboard')
            ->with('success', 'Project berhasil diperbarui.');
    }

    // HAPUS PROJECT
    public function destroy(Project $project)
    {
        $project->delete(); // Pastikan FK ke progresses ON DELETE CASCADE
        return redirect()->route('dig.dashboard')
            ->with('success', 'Project berhasil dihapus.');
    }

    public function progresses(Request $r)
    {
        $status = $r->input('status','all');
        $projects = Project::with(['digitalBanking','developer','progresses.updates','progresses.notes']);

        if ($status==='in_progress') {
            $projects->whereHas('progresses', fn($q)=>$q->whereNull('confirmed_at'));
        } elseif ($status==='done') {
            $projects->whereDoesntHave('progresses', fn($q)=>$q->whereNull('confirmed_at'))
                     ->orWhereHas('progresses', fn($q)=>$q->whereNotNull('confirmed_at'));
        }

        return view('semua.progresses', ['projects'=>$projects->get()]);
    }
}
