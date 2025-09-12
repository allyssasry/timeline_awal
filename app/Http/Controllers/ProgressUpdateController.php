<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ✅ Import the Eloquent models
use App\Models\Progress;
use App\Models\ProgressUpdate;
use App\Models\ProgressNote;

class ProgressUpdateController extends Controller
{
    public function confirm(Progress $progress)
    {
        $progress->update(['confirmed_at' => now()]);
        return back()->with('success', 'Progress berhasil dikonfirmasi.');
    }

    public function storeUpdate(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
            'note'        => ['nullable','string','max:1000'],
        ]);

        ProgressUpdate::updateOrCreate(
            ['progress_id' => $progress->id, 'update_date' => $data['update_date']],
            [
                'percent'    => $data['percent'],
                'note'       => $data['note'] ?? null,
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Update progress tersimpan.');
    }

    public function storeNote(Request $request, Progress $progress)
    {
        $data = $request->validate(['body' => ['required','string','max:1000']]);

        ProgressNote::create([
            'progress_id' => $progress->id,
            'user_id'     => Auth::id(),
            'role'        => optional(Auth::user())->role, // 'digital_banking' / 'it'
            'body'        => $data['body'],
        ]);

        return back()->with('success', 'Catatan ditambahkan.');
    }


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
