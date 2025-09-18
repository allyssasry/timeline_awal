<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DigNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('dig.notifications', [
            'unreadCount'   => $user->unreadNotifications()->count(),
            'today'         => $user->notifications()->whereDate('created_at', now()->toDateString())->latest()->get(),
            'notifications' => $user->notifications()->latest()->paginate(20),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function markRead(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        return back();
    }
    
}
