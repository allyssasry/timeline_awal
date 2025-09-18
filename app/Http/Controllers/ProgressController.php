<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Progress;
use Illuminate\Http\Request;

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

        $project->progresses()->create($data);

        return back()->with('success','Progress berhasil ditambahkan.');
    }

    /**
     * PUT /progresses/{progress}
     * Edit progress (form inline).
     */
    public function update(Request $request, Progress $progress)
    {
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
        $progress->delete();

        return back()->with('success', 'Progress berhasil dihapus.');
    }

    /**
     * POST /progresses/{progress}/confirm
     * Konfirmasi progress jika realisasi >= target.
     */
 // app/Http/Controllers/ProgressController.php
public function confirm(Progress $progress)
{
    // (opsional) validasi: hanya boleh konfirmasi kalau realisasi >= target
    $latest = optional(
        $progress->updates()->orderByDesc('update_date')->first()
    )->percent ?? 0;

    if ($latest < (int)$progress->desired_percent) {
        return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
    }

    // tandai progress ini confirmed
    if (is_null($progress->confirmed_at)) {
        $progress->forceFill(['confirmed_at' => now()])->save();
    }

    // === CEK SEMUA PROGRESS di project ===
    $project = $progress->project()->with(['progresses' => function($q){
        $q->with(['updates' => fn($u) => $u->orderByDesc('update_date')]);
    }])->first();

    $allDone = $project->progresses->every(function($pr){
        $last = $pr->updates->first(); // sudah di-order desc
        $real = (int) ($last->percent ?? $last->progress_percent ?? 0);
        return $pr->confirmed_at && $real >= (int)$pr->desired_percent;
    });

    if ($allDone && is_null($project->finished_at)) {
        $project->finished_at = now();
        $project->save();
    }

    return back()->with('success', $allDone ? 'Project selesai dikonfirmasi.' : 'Progress dikonfirmasi.');
}

}
