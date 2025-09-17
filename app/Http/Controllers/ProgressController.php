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
    public function confirm(Progress $progress)
    {
        if ($progress->confirmed_at) {
            return back()->with('success', 'Progress ini sudah dikonfirmasi sebelumnya.');
        }

        // Cek update terbaru (handle percent/progress_percent)
        $last   = $progress->updates()->orderByDesc('update_date')->first();
        $latest = (int) ($last->progress_percent ?? $last->percent ?? 0);

        if ($latest < (int) $progress->desired_percent) {
            return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
        }

        $progress->forceFill(['confirmed_at' => now()])->save();

        return back()->with('success', 'Progress berhasil dikonfirmasi.');
    }
}
