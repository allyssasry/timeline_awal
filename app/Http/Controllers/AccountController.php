<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Tampilkan form pengaturan akun */
    public function edit()
    {
        $user = Auth::user()->fresh();

        // Guess first/last name dari field "name" jika kolom first_name/last_name kosong
        [$firstGuess, $lastGuess] = ['', ''];
        if ($user->name) {
            $parts = preg_split('/\s+/', $user->name, -1, PREG_SPLIT_NO_EMPTY);
            $firstGuess = $parts ? array_shift($parts) : '';
            $lastGuess  = $parts ? implode(' ', $parts) : '';
        }

        return view('account.setting', [
            'user'        => $user,
            'first_guess' => $firstGuess,
            'last_guess'  => $lastGuess,
            'avatarUrl'   => $this->avatarUrl($user),
        ]);
    }

    /** Simpan perubahan */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'gender'      => ['nullable', Rule::in(['male','female'])],
            'first_name'  => ['nullable', 'string', 'max:100'],
            'last_name'   => ['nullable', 'string', 'max:100'],
            'address'     => ['nullable', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users','email')->ignore($user->id)],
            'username'    => ['nullable', 'string', 'max:100', Rule::unique('users','username')->ignore($user->id)],
            'phone'       => ['nullable', 'string', 'max:50'],
            'avatar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB
        ]);

        $update = [
            'gender'     => $validated['gender']      ?? $user->gender,
            'first_name' => $validated['first_name']  ?? $user->first_name,
            'last_name'  => $validated['last_name']   ?? $user->last_name,
            'address'    => $validated['address']     ?? $user->address,
            'email'      => $validated['email']       ?? $user->email,
            'username'   => $validated['username']    ?? $user->username,
            'phone'      => $validated['phone']       ?? $user->phone,
        ];

        // Opsional: sinkronkan "name" gabungan first+last (kalau di proyek Anda pakai kolom ini)
        $fullName = trim(($update['first_name'] ?? '').' '.($update['last_name'] ?? ''));
        if ($fullName !== '') {
            $update['name'] = $fullName;
        }

        // Upload avatar (opsional)
        if ($request->hasFile('avatar')) {
            $file     = $request->file('avatar');
            $dir      = 'avatars';
            $filename = 'u_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path     = $file->storeAs($dir, $filename, 'public');

            // Hapus avatar lama bila ada
            foreach (['avatar','avatar_path','photo','profile_photo_path'] as $col) {
                if (!empty($user->{$col}) && Storage::disk('public')->exists($user->{$col})) {
                    try { Storage::disk('public')->delete($user->{$col}); } catch (\Throwable $e) {}
                }
            }

            // Simpan ke salah satu kolom avatar. Kita gunakan 'avatar' sebagai standar.
            $update['avatar'] = $path;
        }

        // Simpan
        $user->fill($update)->save();

        // Refresh objek user & sinkronkan ke session agar view menampilkan data terbaru
        $user->refresh();
        Auth::setUser($user);

        return back()->with('success', 'Perubahan akun tersimpan.');
    }

    /** Bangun URL avatar + cache buster agar foto baru langsung tampil */
    private function avatarUrl($user): ?string
    {
        foreach (['avatar','avatar_path','photo','profile_photo_path'] as $col) {
            if (!empty($user->{$col})) {
                $p = $user->{$col};
                try {
                    $v = Storage::disk('public')->exists($p) ? '?v='.Storage::disk('public')->lastModified($p) : '';
                } catch (\Throwable $e) { $v = ''; }
                return Storage::disk('public')->url($p).$v;
            }
        }
        return null;
    }
}
