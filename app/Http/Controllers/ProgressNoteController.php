<?php

// app/Http/Controllers/ProgressNoteController.php
namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\ProgressNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressNoteController extends Controller
{
    public function store(Request $request, Progress $progress)
    {
        $data = $request->validate([
            // role diisi otomatis dari role user login (digital_banking / it) → dipetakan ke 'digital' / 'it'
            'body' => ['required','string','max:2000'],
        ]);

        $user = Auth::user();
        $role = ($user && $user->role === 'it') ? 'it' : 'digital';

        ProgressNote::create([
            'progress_id' => $progress->id,
            'role'        => $role,
            'body'        => $data['body'],
            'created_by'  => $user?->id ?? 0,
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }
}
