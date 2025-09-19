<?php
// app/Http/Controllers/ProgressController.php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProgressConfirmed;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- IMPORT WAJIB

class ProgressController extends Controller
{
    use AuthorizesRequests; // <-- agar $this->authorize() tersedia

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

    /**
     * Alias untuk kompatibilitas.
     */
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
     * route name yang dipakai di view: progresses.updates.store
     */
    public function updatesStore(Request $request, Progress $progress)
    {
        $this->authorize('manage', $progress);

        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
        ]);

        // pastikan relasi updates() ada di model Progress
        $progress->updates()->create([
            'update_date' => $data['update_date'],
            'percent'     => $data['percent'],
            'created_by'  => Auth::id(),
        ]);

        return back()->with('success', 'Update progress disimpan.');
    }

    /**
     * POST /progresses/{progress}/notes
     * Simpan catatan. (Biasanya boleh untuk DIG & IT, bukan hanya pemilik)
     * route name di view: progresses.notes.store
     */
    public function notesStore(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'body' => ['required','string','max:2000'],
        ]);

        // pastikan relasi notes() ada di model Progress
        $progress->notes()->create([
            'body'    => $data['body'],
            'user_id' => Auth::id(),
            'role'    => Auth::user()->role ?? null, // 'digital_banking' atau 'it'
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

        // Ambil realisasi terbaru (aman terhadap null)
        $latestUpdate = $progress->updates()->orderByDesc('update_date')->first();
        $latest = (int) (
            optional($latestUpdate)->percent
            ?? optional($latestUpdate)->progress_percent
            ?? 0
        );

        if ($latest < (int)$progress->desired_percent) {
            return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
        }

        $progress->forceFill(['confirmed_at' => now()])->save();

        // Notifikasi DIG jika dikonfirmasi oleh IT
        $confirmer     = Auth::user();
        $project       = $progress->project;
        $developerUser = optional($project)->developer;
        $digitalUser   = optional($project)->digitalBanking;

        if (optional($project->developer)->role === 'it'
    && optional($confirmer)->role === 'it'
    && $digitalUser) {
    $digitalUser->notify(new \App\Notifications\ProgressConfirmed($progress, $confirmer));
}

        // Cek semua progress
        $project = $progress->project()->with([
            'progresses' => function ($q) {
                $q->with(['updates' => fn($u) => $u->orderByDesc('update_date')]);
            }
        ])->first();

        $allDone = $project->progresses->every(function ($pr) {
            $last = $pr->updates->first();
            $real = (int)(optional($last)->percent ?? optional($last)->progress_percent ?? 0);
            return $pr->confirmed_at && $real >= (int)$pr->desired_percent;
        });

        if ($allDone && is_null($project->finished_at)) {
            $project->forceFill(['finished_at' => now()])->save();
        }

        return back()->with('success', $allDone ? 'Project selesai dikonfirmasi.' : 'Progress selesai dikonfirmasi.');
    }
}
