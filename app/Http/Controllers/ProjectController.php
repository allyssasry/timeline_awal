<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\SupervisorNotification;

class ProjectController extends Controller
{
    /** Modal tambah project (DIG) */
    public function create()
    {
        $digitalUsers = User::where('role', 'digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role', 'it')->orderBy('name')->get(['id','name','username']);

        $projects = Project::with([
            'creator:id,name',
            'progresses' => fn($q) => $q->with('creator:id,name,role')->withCount('updates'),
        ])->withCount('progresses')->latest()->get();

        return view('dig.dashboard', compact('projects', 'digitalUsers', 'itUsers'))
               ->with('openCreateModal', true);
    }

    /** Dashboard list */
    public function index()
    {
        $projects = Project::with([
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => function ($q) {
                $q->with([
                    'creator:id,name,role',
                    'updates' => fn ($uq) => $uq->latest(),
                ]);
            },
        ])->latest()->get();

        $digitalUsers = User::where('role', 'digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role', 'it')->orderBy('name')->get(['id','name','username']);

        return view('dig.dashboard', compact('projects','digitalUsers','itUsers'));
    }

    /**
     * Simpan project (khusus DIG) + progress awal.
     * Kirim:
     * - DB notification ke IT (developer)
     * - DB notification ke semua Supervisor (PROJECT_CREATED_BY_DIG)
     */
    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'digital_banking') {
            abort(403, 'Hanya Digital Banking yang dapat membuat project.');
        }

        $data = $request->validate([
            'name'                          => ['required','string','max:255'],
            'description'                   => ['nullable','string'],
            'digital_banking_id'            => ['required','exists:users,id'],
            'developer_id'                  => ['required','exists:users,id'],
            'progresses'                    => ['required','array','min:1'],
            'progresses.*.name'             => ['required','string','max:255'],
            'progresses.*.start_date'       => ['required','date'],
            'progresses.*.end_date'         => ['required','date','after_or_equal:progresses.*.start_date'],
            'progresses.*.desired_percent'  => ['required','integer','min:0','max:100'],
        ]);

        $project = DB::transaction(function () use ($data) {
            // Buat project
            $project = Project::create([
                'name'               => $data['name'],
                'description'        => $data['description'] ?? null,
                'created_by'         => Auth::id(),
                'digital_banking_id' => $data['digital_banking_id'],
                'developer_id'       => $data['developer_id'],
            ]);

            // Progress awal (penting: created_by diisi)
            foreach ($data['progresses'] as $p) {
                $project->progresses()->create([
                    'name'            => $p['name'],
                    'start_date'      => $p['start_date'],
                    'end_date'        => $p['end_date'],
                    'desired_percent' => $p['desired_percent'],
                    'created_by'      => Auth::id(), // agar “dibuat oleh DIG”
                ]);
            }

            return $project;
        });

        // === Notif ke IT (database notifications) ===
        if ($developer = User::find($project->developer_id)) {
            $payload = [
                'type'               => 'dig_project_created',
                'by_role'            => 'digital_banking',
                'target_role'        => 'it',
                'project_id'         => $project->id,
                'project_name'       => $project->name,
                'digital_banking_id' => (string) $project->digital_banking_id,
                'developer_id'       => (string) $project->developer_id,
                'message'            => 'Digital Banking Membuat Project',
                'created_by'         => (int) Auth::id(),
                'late'               => false,
            ];

            // isi UUID jika kolom id notifikasi tidak auto
            $developer->notifications()->create([
                'id'   => (string) Str::uuid(),
                'type' => 'app.dig',
                'data' => $payload,
            ]);
        }

        // === Notif ke semua Supervisor ===
        $supPayload = [
            'type'         => SupervisorNotification::PROJECT_CREATED_BY_DIG,
            'project_id'   => $project->id,
            'project_name' => $project->name,
            'message'      => 'DIG membuat project baru.',
            'when'         => now()->toISOString(),
        ];

        User::where('role','supervisor')->get()
            ->each(fn ($sup) => $sup->notify(new SupervisorNotification($supPayload)));

        // Redirect aman
        $route = \Illuminate\Support\Facades\Route::has('projects.index')
               ? 'projects.index'
               : (\Illuminate\Support\Facades\Route::has('dig.dashboard') ? 'dig.dashboard' : '/');

        return redirect()->route($route)->with('success','Project berhasil dibuat.');
    }

    /** Detail project */
    public function show(Project $project)
    {
        $project->load([
            'progresses.creator:id,name,role',
            'progresses.updates',
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'creator:id,name',
        ]);

        return view('dig.detail', compact('project'));
    }

    /** Edit project */
    public function edit(Project $project)
    {
        $digitalUsers = User::where('role','digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role','it')->orderBy('name')->get(['id','name','username']);

        return view('projects.edit', compact('project','digitalUsers','itUsers'));
    }

    /** Update project */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'               => ['required','string','max:255'],
            'digital_banking_id' => ['required','exists:users,id'],
            'developer_id'       => ['required','exists:users,id'],
            'description'        => ['nullable','string'],
        ]);

        $project->update($data);

        $route = \Illuminate\Support\Facades\Route::has('dig.dashboard') ? 'dig.dashboard' : '/';

        return redirect()->route($route)->with('success','Project berhasil diperbarui.');
    }

    /** Hapus project */
    public function destroy(Project $project)
    {
        $project->delete(); // pastikan FK progresses ON DELETE CASCADE

        $route = \Illuminate\Support\Facades\Route::has('dig.dashboard') ? 'dig.dashboard' : '/';

        return redirect()->route($route)->with('success','Project berhasil dihapus.');
    }

    /** Halaman semua progress (versi “semua”) */
    public function progresses(Request $r)
    {
        $status = $r->input('status','all');

        $projects = Project::with([
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses.updates',
            'progresses.notes',
        ]);

        if ($status === 'in_progress') {
            $projects->whereHas('progresses', fn($q) => $q->whereNull('confirmed_at'));
        } elseif ($status === 'done') {
            $projects->whereDoesntHave('progresses', fn($q) => $q->whereNull('confirmed_at'))
                     ->orWhereHas('progresses', fn($q) => $q->whereNotNull('confirmed_at'));
        }

        return view('semua.progresses', ['projects' => $projects->latest()->get()]);
    }
}
