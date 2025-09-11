<?php
   // app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function digDashboard()
    {
        // Project terlihat untuk Digital Banking (bisa semua atau filter by digital_banking_id = me)
        $projects = Project::with(['creator','progresses.updates'])->orderByDesc('id')->get();

        // dropdown penanggung jawab saat bikin project
        $digitalUsers = User::where('role','digital_banking')->orderBy('name')->get();
        $itUsers      = User::where('role','it')->orderBy('name')->get();

        return view('dig.dashboard', compact('projects','digitalUsers','itUsers'));
    }

    public function itDashboard()
    {
        // Anak IT melihat semua project dari Digital Banking + progres & update
        $projects = Project::with(['digitalBanking','developer','progresses.updates'])->orderByDesc('id')->get();

        return view('it.dashboard', compact('projects'));
    }
}

