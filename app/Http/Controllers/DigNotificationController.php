<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
// ⬇️ Tambahan import:
use App\Models\Project;
use App\Models\User;
use App\Notifications\DigMarkedReadNotification;

class DigNotificationController extends Controller
{
    /**
     * ===================== DIG (Digital Banking) =====================
     * Halaman notifikasi untuk user role DIG.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        [$startUtc, $endUtc, $startJak, $endJak] = $this->todayRangeUtcWithLocal();

        // Ambil semua notifikasi "hari ini" (Blade bebas memfilter)
        $todayAll = $user->notifications()
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->latest()
            ->get();

        // Semua notifikasi (arsip)
        $allPaginated = $user->notifications()
            ->latest()
            ->paginate(20);

        return view('dig.notifications', [
            'unreadCount'          => $user->unreadNotifications()->count(),
            'today'                => $todayAll,
            'notifications'        => $allPaginated,
            'today_start_jakarta'  => $startJak,
            'today_end_jakarta'    => $endJak,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // Pastikan notifikasi milik user yang login
        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        /**
         * === Kirim notifikasi ke IT saat DIG menandai baca ===
         * Ambil info developer/IT dari payload notifikasi (jika ada),
         * jika tidak ada, fallback lewat Project.
         */
        $data        = $n->data ?? [];
        $projectId   = data_get($data, 'project_id');
        $projectName = data_get($data, 'project_name');
        $developerId = data_get($data, 'developer_id');

        // Fallback cari developer_id dari Project
        if (!$developerId && $projectId) {
            $developerId = Project::whereKey($projectId)->value('developer_id');
        }

        // Fallback cari nama project
        if ($projectId && !$projectName) {
            $projectName = optional(Project::find($projectId))->name;
        }

        // Kirim notif ke user IT yang bersangkutan
        if ($developerId) {
            $it = User::find($developerId);
            if ($it && $it->id !== $user->id && $it->role === 'it') {
                $it->notify(new DigMarkedReadNotification([
                    'project_id'  => $projectId,
                    'project_name'=> $projectName,
                    'by_user_id'  => $user->id,
                    'by_name'     => $user->name,
                    'by_role'     => 'digital_banking',
                    'message'     => 'Digital Banking telah menandai notifikasi sebagai terbaca.',
                    'source_notification_id' => $n->id,
                ]));
            }
        }

        return back()->with('success', 'Notifikasi ditandai terbaca.');
    }

    /**
     * ===================== IT (Developer) =====================
     * Halaman notifikasi untuk user role IT.
     *
     * Syarat yang masuk ke IT:
     * - data->target_role = "it"
     * - data->type in ["dig_project_created", "dig_marked_read"]
     */
    public function itIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        [$startUtc, $endUtc, $startJak, $endJak] = $this->todayRangeUtcWithLocal();

        $base = $user->notifications()
            ->where('data->target_role', 'it')
            ->whereIn('data->type', ['dig_project_created', 'dig_marked_read']);

        // Hari ini (IT)
        $today = (clone $base)
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->latest()
            ->get();

        // Semua (IT)
        $allPaginated = (clone $base)
            ->latest()
            ->paginate(20);

        return view('it.notifications', [
            'unreadCount'          => $user->unreadNotifications()
                                          ->where('data->target_role', 'it')
                                          ->whereIn('data->type', ['dig_project_created','dig_marked_read'])
                                          ->count(),
            'today'                => $today,
            'notifications'        => $allPaginated,
            'today_start_jakarta'  => $startJak,
            'today_end_jakarta'    => $endJak,
        ]);
    }

    public function itMarkAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // Tandai hanya notifikasi IT
        $user->unreadNotifications()
            ->where('data->target_role', 'it')
            ->whereIn('data->type', ['dig_project_created','dig_marked_read'])
            ->get()
            ->markAsRead();

        return back()->with('success', 'Semua notifikasi IT ditandai sudah dibaca.');
    }

    public function itMarkRead(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) abort(401);

        $n = $user->notifications()
            ->where('id', $id)
            ->where('data->target_role', 'it')
            ->whereIn('data->type', ['dig_project_created','dig_marked_read'])
            ->firstOrFail();

        $n->markAsRead();

        return back();
    }

    /**
     * Helper: hitung rentang "hari ini" di Asia/Jakarta, kembalikan juga versi UTC.
     *
     * @return array{0:\DateTimeInterface,1:\DateTimeInterface,2:Carbon,3:Carbon}
     */
    private function todayRangeUtcWithLocal(): array
    {
        $tzJakarta = 'Asia/Jakarta';
        $startJak  = Carbon::now($tzJakarta)->startOfDay();
        $endJak    = Carbon::now($tzJakarta)->endOfDay();

        $startUtc  = $startJak->clone()->timezone('UTC');
        $endUtc    = $endJak->clone()->timezone('UTC');

        return [$startUtc, $endUtc, $startJak, $endJak];
    }
}
