<?php
// app/Http/Controllers/ProgressController.php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str; // <-- ditambahkan

// Notifikasi
use App\Notifications\ProgressConfirmed;            // notif ke DIG saat IT konfirmasi
use App\Notifications\SupervisorStatusNotification; // notif ke Supervisor (it_done, dig_done, project_done)

class ProgressController extends Controller
{
    use AuthorizesRequests;

    /**
     * POST /projects/{project}/progresses
     * Tambah progress baru ke project.
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'start_date'      => ['required','date'],
            'end_date'        => ['required','date','after_or_equal:start_date'],
            'desired_percent' => ['required','integer','min:0','max:100'],
        ]);

        $data['created_by'] = Auth::id();
        $project->progresses()->create($data);

        return back()->with('success','Progress berhasil ditambahkan.');
    }

    /** Alias kompatibilitas. */
    public function storeForProject(Request $request, Project $project)
    {
        return $this->store($request, $project);
    }

    /**
     * PUT /progresses/{progress}
     * Edit metadata progress. Hanya pemilik (created_by) yang boleh.
     */
    public function update(Request $request, Progress $progress)
    {
        $this->authorize('manage', $progress);

        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'start_date'      => ['required','date'],
            'end_date'        => ['required','date','after_or_equal:start_date'],
            'desired_percent' => ['required','integer','min:0','max:100'],
        ]);

        $progress->update($data);

        return back()->with('success', 'Progress berhasil diperbarui.');
    }

    /**
     * DELETE /progresses/{progress}
     * Hapus progress. Hanya pemilik yang boleh.
     */
    public function destroy(Progress $progress)
    {
        $this->authorize('manage', $progress);

        $progress->delete();

        return back()->with('success', 'Progress berhasil dihapus.');
    }

    /**
     * POST /progresses/{progress}/updates
     * Simpan update harian (realisasi %). Hanya pemilik progress yang boleh update.
     * route name di view: progresses.updates.store
     */
    public function updatesStore(Request $request, Progress $progress)
    {
        $this->authorize('manage', $progress);

        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
        ]);

        $progress->updates()->create([
            'update_date' => $data['update_date'],
            'percent'     => $data['percent'],
            'created_by'  => Auth::id(),
        ]);

        return back()->with('success', 'Update progress disimpan.');
    }

    /**
     * POST /progresses/{progress}/notes
     * Simpan catatan. (Biasanya boleh untuk DIG & IT)
     * route name: progresses.notes.store
     */
    public function notesStore(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'body' => ['required','string','max:2000'],
        ]);

        // GUNAKAN kolom yang ada pada tabel notes mu:
        // Jika kolomnya 'content', pakai 'content'. Jika 'body', ganti jadi 'body'.
        $progress->notes()->create([
            'content' => $data['body'],              // <- ganti ke 'body' jika kolomnya 'body'
            'user_id' => Auth::id(),
            'role'    => Auth::user()->role ?? null, // 'digital_banking' | 'it'
        ]);

        return back()->with('success', 'Catatan ditambahkan.');
    }

    /**
     * POST /progresses/{progress}/confirm
     * Konfirmasi progress jika realisasi >= target. Hanya pemilik yang boleh.
     */
    public function confirm(Progress $progress)
    {
        $this->authorize('manage', $progress);

        if ($progress->confirmed_at) {
            return back()->with('success', 'Progress sudah dikonfirmasi sebelumnya.');
        }

        // Ambil realisasi terbaru
        $latestUpdate = $progress->updates()->orderByDesc('update_date')->first();
        $latest = (int) (
            optional($latestUpdate)->percent
            ?? optional($latestUpdate)->progress_percent
            ?? 0
        );

        if ($latest < (int) $progress->desired_percent) {
            return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
        }

        $progress->forceFill(['confirmed_at' => now()])->save();

        // ===== Notifikasi ke DIG jika IT yang konfirmasi (punyamu) =====
        $confirmer = Auth::user();
        $project   = $progress->project()->with(['digitalBanking','developer'])->first();

        if (
            optional($project->developer)->role === 'it' &&
            optional($confirmer)->role === 'it' &&
            $project->digitalBanking
        ) {
            $project->digitalBanking->notify(new ProgressConfirmed($progress, $confirmer));
        }

        // ===== Re-load project + seluruh progress beserta latest update =====
        $project = $progress->project()->with([
            'progresses' => function ($q) {
                $q->with([
                    'creator',
                    'updates' => fn($u) => $u->orderByDesc('update_date')
                ]);
            },
            'digitalBanking',
            'developer',
        ])->first();

        // Semua progress selesai?
        $allDone = $project->progresses->every(function ($pr) {
            $last = $pr->updates->first();
            $real = (int) (optional($last)->percent ?? optional($last)->progress_percent ?? 0);
            return $pr->confirmed_at && $real >= (int) $pr->desired_percent;
        });

        if ($allDone && is_null($project->finished_at)) {
            $project->forceFill(['finished_at' => now()])->save();
        }

        // ===== Notifikasi SUPERVISOR =====
        $itGroup  = $project->progresses->filter(fn($p) => ($p->creator?->role ?? null) === 'it');
        $digGroup = $project->progresses->filter(fn($p) => ($p->creator?->role ?? null) === 'digital_banking');

        $itDone  = $itGroup->isNotEmpty()  && $itGroup->every(fn($p) => (bool) $p->confirmed_at);
        $digDone = $digGroup->isNotEmpty() && $digGroup->every(fn($p) => (bool) $p->confirmed_at);

        $payloadCommon = [
            'project_id'   => $project->id,
            'project_name' => $project->name,
            'when'         => now()->toISOString(),
        ];

        $supervisors = User::where('role','supervisor')->get();
        $notifySup = function (array $payload) use ($supervisors) {
            foreach ($supervisors as $sup) {
                $sup->notify(new SupervisorStatusNotification($payload));
            }
        };

        if ($itDone && $digDone) {
            $notifySup(array_merge($payloadCommon, ['status' => 'project_done']));
        } elseif ($itDone) {
            $notifySup(array_merge($payloadCommon, ['status' => 'it_done']));
        } elseif ($digDone) {
            $notifySup(array_merge($payloadCommon, ['status' => 'dig_done']));
        }

        return back()->with('success', $allDone ? 'Project selesai dikonfirmasi.' : 'Progress selesai dikonfirmasi.');
    }
}
