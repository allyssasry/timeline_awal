<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * POST /projects/{project}/progresses
     * Tambah progress ke project.
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
     public function confirm(Progress $progress)
    {
        if ($progress->confirmed_at) {
            return back()->with('success', 'Project sudah dikonfirmasi sebelumnya.');
        }

        // Boleh konfirmasi kalau update terbaru >= target
        $latest = optional($progress->updates()->orderByDesc('update_date')->first())->percent ?? 0;
        if ($latest < (int)$progress->desired_percent) {
            return back()->withErrors(['Konfirmasi gagal: realisasi belum mencapai target.']);
        }

        $progress->forceFill(['confirmed_at' => now()])->save();

        return back()->with('success', 'Project selesai dikonfirmasi.');
    }
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

    public function destroy(Progress $progress)
    {
        $projectId = $progress->project_id; // kalau perlu redirect ke halaman project
        $progress->delete();
        return back()->with('success', 'Progress berhasil dihapus.');
    }
}
