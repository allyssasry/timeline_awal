<?php

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

        // Rentang "hari ini" (Asia/Jakarta)
        $tz = 'Asia/Jakarta';
        $startJak = Carbon::now($tz)->startOfDay();
        $endJak   = Carbon::now($tz)->endOfDay();
        $startUtc = $startJak->clone()->timezone('UTC');
        $endUtc   = $endJak->clone()->timezone('UTC');

        $today = $user->notifications()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->latest()->get();

        $allPaginated = $user->notifications()->latest()->paginate(20);

        return view('it.notifications', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'today'       => $today,
            'notifications' => $allPaginated,
        ]);
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
