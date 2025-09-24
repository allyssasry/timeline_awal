<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SupervisorNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // hanya notifikasi untuk supervisor dari tipe kita
        $base = $user->notifications()
            ->where('data->target_role', 'supervisor')
            ->where('data->type', 'supervisor_status');

        $todayStart = Carbon::now('Asia/Jakarta')->startOfDay()->timezone('UTC');
        $todayEnd   = Carbon::now('Asia/Jakarta')->endOfDay()->timezone('UTC');

        $today = (clone $base)->whereBetween('created_at', [$todayStart, $todayEnd])
                              ->latest()->get();

        $all   = (clone $base)->latest()->paginate(20);

        return view('supervisor.notifications', [
            'today' => $today,
            'notifications' => $all,
            'unreadCount' => $user->unreadNotifications()
                                  ->where('data->target_role','supervisor')
                                  ->where('data->type','supervisor_status')
                                  ->count(),
        ]);
    }
}
