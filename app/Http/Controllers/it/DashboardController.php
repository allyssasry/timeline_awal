<?php
// app/Http/Controllers/It/DashboardController.php
// app/Http/Controllers/It/DashboardController.php
namespace App\Http\Controllers\It;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']); // tambahkan middleware role kalau ada
    }

    public function index()
    {
        $me = auth()->user();

        $projects = Project::with([
                'digitalBanking:id,name,username',
                'developer:id,name,username',
                'progresses' => fn($q) => $q->with([
                    'creator:id,name,role',
                    'updates' => fn($u) => $u->orderByDesc('update_date'),
                ]),
            ])
            ->where(fn($q) => $q->where('developer_id', $me->id)
                                ->orWhere('created_by', $me->id))
            ->latest('id')
            ->get();

        // === SEMUA USER DIGITAL BANKING MUNCUL DI DROPDOWN ===
        $digUsers = User::where('role','digital_banking')
                        ->orderBy('name')
                        ->get(['id','name','username']);

        // Opsional (kalau ada konsep "anak DB" di tabel, contoh kolom supervisor_id):
        // $digUsers = User::where('role','digital_banking')
        //                 ->when($me->role === 'supervisor', fn($q) => $q->where('supervisor_id', $me->id))
        //                 ->orderBy('name')
        //                 ->get(['id','name','username']);

        $itUsers  = User::where('role','it')
                        ->orderBy('name')
                        ->get(['id','name','username']);

        return view('it.dashboard', compact('projects','digUsers','itUsers'));
    }
}
