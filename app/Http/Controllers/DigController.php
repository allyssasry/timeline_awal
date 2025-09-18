<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User; // ⬅️ tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DigController extends Controller
{
    public function dashboard()
    {
        $projects = Project::with([
            'progresses.updates' => fn($q) => $q->orderByDesc('update_date'),
            'digitalBanking',
            'developer',
        ])->latest()->get();

        // ⬇️ ambil user sesuai role
        $digitalUsers = User::where('role', 'digital_banking')->orderBy('name')->get();
        $itUsers      = User::where('role', 'it')->orderBy('name')->get();

        return view('dig.dashboard', compact('projects','digitalUsers','itUsers'));
    }
    
public function notifications()
{
    $user = Auth::user();

    // Hanya notifikasi "Progress dikonfirmasi oleh IT."
    $query = $user->notifications()
        ->whereJsonContains('data->message', 'Progress dikonfirmasi oleh IT.')
        ->latest();

    $today = (clone $query)->whereDate('created_at', today())->get();
    $unreadCount = (clone $query)->whereNull('read_at')->count();

    return view('dig.notifications', compact('today','unreadCount'));
}
}
