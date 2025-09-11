<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\Request;
use App\Models\ProgressUpdate; // <— PENTING: import model

use Illuminate\Support\Facades\Auth;

class ProgressUpdateController extends Controller
{
    // POST /progresses/{progress}/updates
    public function store(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
            'note'        => ['nullable','string','max:1000'],
        ]);

        // upsert by (progress_id, update_date) — hindari duplikat
        $update = ProgressUpdate::updateOrCreate(
            [
                'progress_id' => $progress->id,
                'update_date' => $data['update_date'],
            ],
            [
                'percent'    => $data['percent'],
                'note'       => $data['note'] ?? null,
                'updated_by' => Auth::id() ?? 0,
            ]
        );

        return back()->with(
            'success',
            $update->wasRecentlyCreated ? 'Update progress ditambahkan.' : 'Update progress diperbarui.'
        );
    }


    /**
     * GET /progress-updates/{progressUpdate}/edit
     * (opsional) form edit update harian
     */
    public function edit(ProgressUpdate $progressUpdate)
    {
        return view('progress.edit', compact('progressUpdate'));
    }

    /**
     * PUT/PATCH /progress-updates/{progressUpdate}
     * Update data update harian.
     */
    public function update(Request $request, ProgressUpdate $progressUpdate)
    {
        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
            'note'        => ['nullable','string','max:2000'],
        ]);

        $progressUpdate->update([
            'update_date' => $data['update_date'],
            'percent'     => $data['percent'],
            'note'        => $data['note'] ?? null,
        ]);

        return back()->with('success','Update harian diperbarui.');
    }
}
