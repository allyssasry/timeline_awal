<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Notifications\SupervisorNotification as SN;


class SupervisorController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $u = $request->user();
            if (!$u) {
                abort(401);
            }
            if ($u->role !== 'supervisor') {
                abort(403, 'Akses khusus Supervisor.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        return redirect()->route('supervisor.progresses');
    }

    public function progresses(Request $r)
    {
        $status = $r->string('status', 'all')->toString();

        $q = Project::query()
            ->with([
                'digitalBanking:id,name',
                'developer:id,name',
                'progresses' => function ($x) {
                    $x->with(['updates' => fn($u) => $u->orderByDesc('update_date')]);
                }
            ])
            ->latest('id');

        if ($status === 'in_progress') {
            $q->whereHas('progresses', fn($p) => $p->whereNull('confirmed_at'));
        } elseif ($status === 'done') {
            $q->whereDoesntHave('progresses', fn($p) => $p->whereNull('confirmed_at'));
        }

        return view('supervisor.progresses', [
            'projects' => $q->get(),
            'status'   => $status,
        ]);
    }

       public function show(Project $project)
    {
        $project->load([
            'creator:id,name',
            'digitalBanking:id,name,username',
            'developer:id,name,username',
            'progresses' => function ($q) {
                $q->with([
                    'creator:id,name,role',
                    'updates' => fn($uq) => $uq
                        ->orderByDesc('update_date')
                        ->orderByDesc('created_at'),
                    'notes'   => fn($nq) => $nq->latest(), // jika kamu punya tabel notes
                ]);
            },
        ]);

        // ring: rata-rata realisasi dari update terbaru tiap progress
        $latestPercents = [];
        foreach ($project->progresses as $pr) {
            $u = $pr->updates->first();
            $latestPercents[] = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
        }
        $realization = count($latestPercents)
            ? (int) round(array_sum($latestPercents) / max(count($latestPercents), 1))
            : 0;

        // status selesai jika semua progress >= target dan confirmed_at terisi
        $allMetAndConfirmed = $project->progresses->every(function ($p) {
            $u = $p->updates->first();
            $real = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
            return $real >= (int)$p->desired_percent && !is_null($p->confirmed_at);
        });

        // tanggal semua progress selesai (maks confirmed_at) untuk header kanan
        $finishedAt = $allMetAndConfirmed
            ? optional($project->progresses->max('confirmed_at'))->timezone('Asia/Jakarta')
            : null;

        return view('supervisor.show', compact(
            'project', 'realization', 'allMetAndConfirmed', 'finishedAt'
        ));
    }

 

public function notifications(Request $request)
{
    $user = $request->user();

    // Hitung rentang "hari ini" di Asia/Jakarta & konversi ke UTC
    $startJak = Carbon::now('Asia/Jakarta')->startOfDay();
    $endJak   = Carbon::now('Asia/Jakarta')->endOfDay();
    $startUtc = $startJak->clone()->timezone('UTC');
    $endUtc   = $endJak->clone()->timezone('UTC');

    $allowed = [SN::PROJECT_CREATED_BY_DIG, SN::PROJECT_DONE, SN::PROJECT_UNMET];

    $today = $user->notifications()
        ->whereIn('data->type', $allowed)
        ->whereBetween('created_at', [$startUtc, $endUtc])
        ->latest()
        ->get();

    $unreadCount = $user->unreadNotifications()
        ->whereIn('data->type', $allowed)
        ->count();

    return view('supervisor.notifications', compact('today','unreadCount'));
}
}


