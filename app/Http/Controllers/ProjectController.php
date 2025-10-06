<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\SupervisorNotification;
use App\Notifications\DigCompletionDecision;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- tambahkan ini

class ProjectController extends Controller
{
    use AuthorizesRequests; // <-- dan ini

    // NEW: pastikan semua aksi butuh login
    public function __construct() // NEW
    {
        $this->middleware('auth'); // NEW
    } // NEW

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
     * Simpan project (khusus DIG/IT) + progress awal.
     */
    public function store(Request $request)
    {
        $role = auth()->user()?->role;

        if (!in_array($role, ['digital_banking','it'], true)) {
            abort(403, 'Hanya Digital Banking atau IT yang dapat membuat project.');
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

        if ($role === 'it') {
            $data['developer_id'] = auth()->id();
        }

        $project = \DB::transaction(function () use ($data) {
            $project = Project::create([
                'name'               => $data['name'],
                'description'        => $data['description'] ?? null,
                'created_by'         => auth()->id(),
                'digital_banking_id' => $data['digital_banking_id'],
                'developer_id'       => $data['developer_id'],
            ]);

            foreach ($data['progresses'] as $p) {
                $project->progresses()->create([
                    'name'            => $p['name'],
                    'start_date'      => $p['start_date'],
                    'end_date'        => $p['end_date'],
                    'desired_percent' => $p['desired_percent'],
                    'created_by'      => auth()->id(),
                ]);
            }

            return $project;
        });

        if ($role === 'digital_banking') {
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
                    'created_by'         => (int) auth()->id(),
                    'late'               => false,
                ];

                $developer->notifications()->create([
                    'id'   => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'app.dig',
                    'data' => $payload,
                ]);
            }

            $supPayload = [
                'type'         => \App\Notifications\SupervisorNotification::PROJECT_CREATED_BY_DIG,
                'project_id'   => $project->id,
                'project_name' => $project->name,
                'message'      => 'DIG membuat project baru.',
                'when'         => now()->toISOString(),
            ];

            User::where('role','supervisor')->get()
                ->each(fn ($sup) => $sup->notify(new \App\Notifications\SupervisorNotification($supPayload)));
        } else {
            if ($dbUser = User::find($project->digital_banking_id)) {
                $dbUser->notifications()->create([
                    'id'   => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'app.it',
                    'data' => [
                        'type'         => 'it_project_created',
                        'by_role'      => 'it',
                        'project_id'   => $project->id,
                        'project_name' => $project->name,
                        'message'      => 'IT membuat project baru.',
                        'created_by'   => (int) auth()->id(),
                    ],
                ]);
            }
        }

        if ($role === 'it' && \Route::has('it.dashboard')) {
            return redirect()->route('it.dashboard')->with('success','Project berhasil dibuat.');
        }

        $route = \Route::has('projects.index') ? 'projects.index'
                : (\Route::has('dig.dashboard') ? 'dig.dashboard' : '/');

        return redirect()->route($route)->with('success','Project berhasil dibuat.');
    }

    /** Detail project */
    public function show(Project $project)
    {
        $project->load([
            'progresses' => function ($q) {
                $q->with([
                    'creator:id,name,role',
                    'updates' => fn($u) => $u->orderByDesc('update_date'),
                    'notes',
                ]);
            },
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'creator:id,name',
        ]);

        return view('dig.detail', compact('project'));
    }

    /** Edit project */
    public function edit(Project $project)
    {
        $this->authorize('manage', $project);

        $digitalUsers = User::where('role','digital_banking')->orderBy('name')->get(['id','name','username']);
        $itUsers      = User::where('role','it')->orderBy('name')->get(['id','name','username']);

        return view('projects.edit', compact('project','digitalUsers','itUsers'));
    }

    /** Update project */
    public function update(Request $request, Project $project)
    {
        $this->authorize('manage', $project);

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
        $this->authorize('manage', $project);

        $project->delete();

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

    /** Finalisasi: Memenuhi / Tidak Memenuhi (khusus DIG) */
    public function setCompletion(Request $request, Project $project)
    {
        if (auth()->user()?->role !== 'digital_banking') {
            abort(403, 'Hanya Digital Banking yang dapat memutuskan penyelesaian.');
        }

        $data = $request->validate([
            'meets' => ['required','in:0,1'],
        ]);

        if (!is_null($project->meets_requirement)) {
            return back()->with('info', 'Project sudah difinalisasi sebelumnya.');
        }

        $project->load(['progresses.updates' => fn($u) => $u->orderByDesc('update_date')]);
        $allMetAndConfirmed = $project->progresses->every(function ($pr) {
            $latest = $pr->updates->first();
            $real   = $latest ? (int)($latest->percent ?? $latest->progress_percent ?? 0) : 0;
            return $real >= (int)$pr->desired_percent && !is_null($pr->confirmed_at);
        });

        $readyBecauseOverdue = method_exists($project, 'readyBecauseOverdue')
            ? $project->readyBecauseOverdue()
            : false;

        if (!$allMetAndConfirmed && !$readyBecauseOverdue) {
            return back()->with('error', 'Belum memenuhi syarat finalisasi: pastikan semua progress tercapai & terkonfirmasi, atau hanya tersisa progress yang sudah lewat timeline dan belum memenuhi sementara lainnya sudah terkonfirmasi.');
        }

        $project->completed_at      = $project->completed_at ?? now();
        $project->meets_requirement = (bool) ((int) $data['meets']);
        $project->save();

        if ($dev = User::find($project->developer_id)) {
            $dev->notify(new DigCompletionDecision(
                projectId:   $project->id,
                projectName: $project->name,
                meets:       $project->meets_requirement,
                byId:        auth()->id(),
                byName:      auth()->user()?->name
            ));
        }

        $notifType = $project->meets_requirement
            ? SupervisorNotification::PROJECT_DONE
            : SupervisorNotification::PROJECT_UNMET;

        $supPayload = [
            'type'         => $notifType,
            'project_id'   => $project->id,
            'project_name' => $project->name,
            'message'      => $project->meets_requirement
                                ? 'DIG memutuskan: Memenuhi.'
                                : 'DIG memutuskan: Tidak Memenuhi.',
            'when'         => now()->toISOString(),
        ];

        User::where('role','supervisor')->get()
            ->each(fn ($sup) => $sup->notify(new SupervisorNotification($supPayload)));

        return back()->with('success', 'Status penyelesaian project diperbarui.');
    }
}
