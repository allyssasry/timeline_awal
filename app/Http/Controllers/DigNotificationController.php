<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DigNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Pastikan user ada
        if (!$user) {
            abort(401);
        }
        

        // Hitung rentang "hari ini" menurut Asia/Jakarta,
        // lalu konversi ke UTC untuk query (umumnya created_at tersimpan UTC).
        $tzJakarta = 'Asia/Jakarta';
        $startJak  = Carbon::now($tzJakarta)->startOfDay();
        $endJak    = Carbon::now($tzJakarta)->endOfDay();

        $startUtc  = $startJak->clone()->timezone('UTC');
        $endUtc    = $endJak->clone()->timezone('UTC');

        // Kumpulan notifikasi "hari ini" (tanpa filter tipe/role, biar Blade-mu yang filter)
        $todayAll = $user->notifications()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->latest()
            ->get();

        // Semua notifikasi (paginated) untuk arsip/riwayat
        $allPaginated = $user->notifications()
            ->latest()
            ->paginate(20);

        return view('dig.notifications', [
            'unreadCount'   => $user->unreadNotifications()->count(),
            // KOMPAT dengan variabel yang dipakai di Blade:
            'today'         => $todayAll,
            'notifications' => $allPaginated,
            // (opsional) kirim juga info batas waktu hari ini di Jakarta untuk debugging/keperluan UI
            'today_start_jakarta' => $startJak,  // Carbon instance
            'today_end_jakarta'   => $endJak,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        // Pastikan notifikasi milik user yang login
        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back();
    }
}
