<?php

namespace App\Http\Controllers;

use App\Models\Project;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        // Ambil SEMUA project + relasi yang diperlukan untuk tampilan
        $projects = Project::with([
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => function ($q) {
                $q->with([
                    // update terbaru untuk realisasi cincin
                    'updates' => fn ($uq) => $uq->latest(),
                    // pembuat progress (untuk label DIG/IT + nama)
                    'creator:id,name,role,username',
                ])->orderBy('start_date');
            },
        ])->latest('id')->get();

        return view('supervisor.dashboard', compact('projects'));
    }
}
