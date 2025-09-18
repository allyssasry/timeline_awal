<?php
// app/Http/Controllers/ProgressController.php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProgressConfirmed;

class ProgressController extends Controller
{
    /**
     * POST /projects/{project}/progresses
     * Tambah progress ke project (dipakai oleh route('projects.progresses.store')).
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'start_date'      => ['required','date'],
            'end_date'        => ['required','date','after_or_equal:start_date'],
            'desired_percent' => ['required','integer','min:0','max:100'],
        ]);

        // dari snippet atas: catat pembuat progress
        $data['created_by'] = Auth::id();

        $project->progresses()->create($data);

        return back()->with('success','Progress berhasil ditambahkan.');
    }

    /**
     * Alias: Tambah progress dari form "Tambah Progress" (di kartu project).
     * Disiapkan untuk kompatibilitas jika rute memanggil storeForProject().
     */
    public function storeForProject(Request $request, Project $project)
    {
        return $this->store($request, $project);
    }

    /**
     * PUT /progresses/{progress}
     * Edit progress (form inline).
     */
    public function update(Request $request, Progress $progress)
    {
        // dari snippet atas: pastikan user berhak
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
     * Hapus progress.
     */
    public function destroy(Progress $progress)
    {
        // dari snippet atas: pastikan user berhak
        $this->authorize('manage', $progress);

        $progress->delete();

        return back()->with('success', 'Progress berhasil dihapus.');
    }

    /**
     * POST /progresses/{progress}/confirm
     * Konfirmasi progress jika realisasi >= target.
     * - Set confirmed_at progress
     * - Kirim notifikasi ke DIG hanya jika developer project = 'it' dan user yang konfirmasi = 'it'
     * - Jika semua progress di project sudah memenuhi target & confirmed, set finished_at project
     */
    public function confirm(Progress $progress)
    {
        // dari snippet atas: pastikan user berhak
        $this->authorize('manage', $progress);

        // Jika sudah pernah dikonfirmasi, tidak usah lanjut
        if ($progress->confirmed_at) {
            return back()->with('success', 'Progress sudah dikonfirmasi sebelumnya.');
        }

        // Ambil realisasi terbaru
        $latestUpdate = $progress->updates()->orderByDesc('update_date')->first();
        $latest = (int)($latestUpdate->percent ?? $latestUpdate->progress_percent ?? 0);

        // Validasi: realisasi harus >= target
        if ($latest < (int)$progress->desired_percent) {
            return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
        }

        // Tandai progress ini confirmed
        $progress->forceFill(['confirmed_at' => now()])->save();

        // ----- Kirim notifikasi ke DIG hanya jika dikerjakan & dikonfirmasi oleh IT -----
        $confirmer     = Auth::user();
        $project       = $progress->project;                  // pastikan relasi progress->project ada
        $developerUser = optional($project)->developer;       // user IT yang ditunjuk di project
        $digitalUser   = optional($project)->digitalBanking;  // user DIG (penerima notifikasi)

        if (optional($developerUser)->role === 'it'
            && optional($confirmer)->role === 'it'
            && $digitalUser) {
            $digitalUser->notify(new ProgressConfirmed($progress, $confirmer));
        }

        // === Cek apakah SEMUA progress di project sudah memenuhi target & confirmed ===
        $project = $progress->project()->with([
            'progresses' => function ($q) {
                $q->with(['updates' => fn($u) => $u->orderByDesc('update_date')]);
            }
        ])->first();

        $allDone = $project->progresses->every(function ($pr) {
            $last = $pr->updates->first(); // sudah di-order desc
            $real = (int)($last->percent ?? $last->progress_percent ?? 0);
            return $pr->confirmed_at && $real >= (int)$pr->desired_percent;
        });

        // Jika semua selesai dan project belum ditandai selesai, set finished_at
        if ($allDone && is_null($project->finished_at)) {
            $project->forceFill(['finished_at' => now()])->save();
        }

        return back()->with('success', $allDone ? 'Project selesai dikonfirmasi.' : 'Progress selesai dikonfirmasi.');
    }
}
