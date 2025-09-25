<?php

// app/Http/Controllers/ItNotificationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ItNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);
        if ($user->role !== 'it') abort(403, 'Khusus pengguna IT.');

        // Jenis notifikasi yang ditampilkan
        $types = ['dig_project_created', 'dig_completion_decision'];

        // Rentang "hari ini" di Asia/Jakarta -> UTC (kolom created_at disimpan UTC)
        $tz       = 'Asia/Jakarta';
        $startJak = Carbon::now($tz)->startOfDay();
        $endJak   = Carbon::now($tz)->endOfDay();
        $startUtc = $startJak->clone()->timezone('UTC');
        $endUtc   = $endJak->clone()->timezone('UTC');

        // Hari ini
        $today = $user->notifications()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->whereIn('data->type', $types)
            ->latest()
            ->get();

        // Badge unread (semua)
        $unreadCount = $user->unreadNotifications()
            ->whereIn('data->type', $types)
            ->count();

        // Semua (untuk pagination/arsip jika dipakai)
        $notifications = $user->notifications()
            ->whereIn('data->type', $types)
            ->latest()
            ->paginate(20);

        return view('it.notifications', compact('today', 'unreadCount', 'notifications'));
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);
        if ($user->role !== 'it') abort(403);

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi IT telah ditandai terbaca.');
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) abort(401);
        if ($user->role !== 'it') abort(403);

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back();
    }
}
