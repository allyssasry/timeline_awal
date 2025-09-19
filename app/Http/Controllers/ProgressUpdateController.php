<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\ProgressUpdate;
use App\Models\ProgressNote;
use App\Notifications\ProgressConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProgressUpdateController extends Controller
{
    /** Menandai progress sudah dikonfirmasi. */
   public function confirm(Progress $progress)
{
    // tandai selesai
    $progress->update(['confirmed_at' => now()]);

    // kirim notifikasi ke PIC Digital Banking dari project terkait
    $digUser = $progress->project?->digitalBanking; // relasi: Project belongsTo(User, 'digital_banking_id')
    if ($digUser) {
        $digUser->notify(new ProgressConfirmed($progress));
    }

    return back()->with('success', 'Progress berhasil dikonfirmasi.');
}


    /**
     * Simpan update harian (STRICT: 1x/hari per progress).
     * Route: progresses.updates.store
     */
    public function store(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'update_date' => [
                'required',
                'date',
                Rule::unique('progress_updates')->where(function ($q) use ($progress) {
                    return $q->where('progress_id', $progress->id);
                }),
            ],
            'percent' => ['required','integer','min:0','max:100'], // ganti ke 'progress_percent' bila kolomnya itu
            'note'    => ['nullable','string','max:1000'],
        ]);

        ProgressUpdate::create([
            'progress_id' => $progress->id,
            'update_date' => $data['update_date'],
            'percent'     => $data['percent'],      // atau 'progress_percent' => $data['percent']
            'note'        => $data['note'] ?? null,
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),            // pastikan kolomnya sudah ada
        ]);
if ($progress->created_by !== auth()->id()) {
    abort(403);
}
        return back()->with('success', 'Progress harian berhasil disimpan.');
    }

    /**
     * Simpan update harian (OVERWRITE jika tanggal sama sudah ada).
     * Route (opsional lain): progresses.updates.upsert
     */
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
                'percent'    => $data['percent'],    // atau 'progress_percent' => $data['percent']
                'note'       => $data['note'] ?? null,
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Update progress tersimpan.');
    }

    /**
     * Simpan catatan progress.
     * Route: progresses.notes.store
     */
    public function storeNote(Request $request, Progress $progress)
    {
        $data = $request->validate([
            'body' => ['required','string','max:1000'],
        ]);

        // Normalisasi role user ke enum/kolom yang valid
        $rawRole = optional(Auth::user())->role; // contoh: 'digital_banking', 'it', 'digital', 'developer', dll.
        $role = null;
        if (in_array($rawRole, ['digital_banking','digital','db','digitalbanking'], true)) {
            $role = 'digital_banking';
        } elseif (in_array($rawRole, ['it','developer','dev'], true)) {
            $role = 'it';
        }

        ProgressNote::create([
            'progress_id' => $progress->id,
            'user_id'     => Auth::id(),   // pastikan migration pakai user_id (bukan created_by)
            'role'        => $role,        // NILAI HARUS SESUAI enum/length kolom
            'body'        => $data['body'],
        ]);

        return back()->with('success', 'Catatan ditambahkan.');
    }

    /** Form edit update harian (opsional). */
    public function edit(ProgressUpdate $progressUpdate)
    {
        return view('progress.edit', compact('progressUpdate'));
    }

    /** Update data update harian. */
    public function update(Request $request, ProgressUpdate $progressUpdate)
    {
        $data = $request->validate([
            'update_date' => ['required','date'],
            'percent'     => ['required','integer','min:0','max:100'],
            'note'        => ['nullable','string','max:2000'],
        ]);

        $progressUpdate->update([
            'update_date' => $data['update_date'],
            'percent'     => $data['percent'],   // atau 'progress_percent' => $data['percent']
            'note'        => $data['note'] ?? null,
        ]);

        return back()->with('success','Update harian diperbarui.');
    }
}
