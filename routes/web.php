<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProgressUpdateController;
// routes/web.php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgressNoteController;
use App\Http\Controllers\DigNotificationController;
use App\Http\Controllers\ItNotificationController;
// routes/web.php
use App\Http\Controllers\SupervisorDashboardController;

use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SupervisorNotificationController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AccountController;
// routes/web.php
use App\Http\Controllers\It\DashboardController as ItDashboard;


Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/dashboard',  [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/progresses', [SupervisorController::class, 'progresses'])->name('progresses');
        Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('notifications'); // ⬅️ ini
        Route::get('/projects/{project}', [SupervisorController::class, 'show'])->name('show');
    });


Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/dashboard',     [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/progresses',    [SupervisorController::class, 'progresses'])->name('progresses');
        Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('notifications');
});

Route::prefix('supervisor')->name('supervisor.')->middleware('auth')->group(function () {
    Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/read-all', [SupervisorController::class, 'notificationsReadAll'])->name('notifications.readAll');

    // (opsional) untuk tandai satu notifikasi
    Route::post('/notifications/{id}/read', [SupervisorController::class, 'notificationsReadOne'])->name('notifications.readOne');
});

Route::patch('/projects/{project}/completion', [ProjectController::class, 'setCompletion'])
    ->name('projects.setCompletion');


// Middleware inline (tanpa Kernel): pastikan role = supervisor
Route::prefix('supervisor')
    ->name('supervisor.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/dashboard',  [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/progresses', [SupervisorController::class, 'progresses'])->name('progresses');

        // Detail project supervisor
        Route::get('/projects/{project}', [SupervisorController::class, 'show'])
            ->name('projects.show');   // ← cukup "projects.show", karena sudah ada prefix 'supervisor.'
    });


Route::middleware(['auth'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])
        ->name('supervisor.dashboard');
});


// IT
Route::get('/it/notifications', [ItNotificationController::class, 'index'])
    ->name('it.notifications')
    ->middleware('auth');

Route::post('/it/notifications/read-all', [ItNotificationController::class, 'markAllRead'])
    ->name('it.notifications.readAll')
    ->middleware('auth');

Route::post('/it/notifications/{id}/read', [ItNotificationController::class, 'markRead'])
    ->name('it.notifications.read')
    ->middleware('auth');

// DIG (kalau ada)
Route::get('/dig/notifications', [DigNotificationController::class, 'index'])
    ->name('dig.notifications')
    ->middleware('auth');
Route::post('/dig/notifications/read-all', [DigNotificationController::class, 'markAllRead'])
    ->name('dig.notifications.readAll')
    ->middleware('auth');
Route::post('/dig/notifications/{id}/read', [DigNotificationController::class, 'markRead'])
    ->name('dig.notifications.read')
    ->middleware('auth');





Route::get('/', function () {
    return view('landing');
});


Route::get('/register', [AuthController::class, 'registerForm'])
    ->name('Auth.register');    // nama rute huruf kecil
Route::post('/register',[AuthController::class,'register']);
Route::get('/login',[AuthController::class,'loginForm'])->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::get('/logout',[AuthController::class,'logout']);


Route::middleware('auth')->group(function () {
    // Dashboard DIG
    Route::get('/dig/dashboard', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

});

Route::middleware('auth')->get('/arsip', function (Request $r) {
    // Ambil semua project + relasi yang dibutuhkan
    $projects = Project::with([
        'digitalBanking',
        'developer',
        'progresses' => function ($q) {
            $q->with(['updates' => fn($u) => $u->orderByDesc('update_date')]);
        },
    ])->get();

    // 1) Filter hanya yang selesai (berdasarkan accessor is_finished)
    $projects = $projects->filter->is_finished;

    // 2) Pencarian (q) di nama/description
    if ($r->filled('q')) {
        $q = mb_strtolower($r->q);
        $projects = $projects->filter(function ($p) use ($q) {
            return str_contains(mb_strtolower($p->name ?? ''), $q)
                || str_contains(mb_strtolower($p->description ?? ''), $q);
        });
    }

    // 3) Filter rentang tanggal berdasarkan finished_at_calc
    if ($r->filled('from')) {
        $projects = $projects->filter(function ($p) use ($r) {
            $d = optional($p->finished_at_calc)?->toDateString();
            return $d && $d >= $r->from;
        });
    }
    if ($r->filled('to')) {
        $projects = $projects->filter(function ($p) use ($r) {
            $d = optional($p->finished_at_calc)?->toDateString();
            return $d && $d <= $r->to;
        });
    }

    // 4) Sorting
    $sort = $r->get('sort', 'finished_desc');
    $projects = match ($sort) {
        'finished_asc' => $projects->sortBy(fn($p) => $p->finished_at_calc ?? $p->updated_at),
        'name_asc'     => $projects->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
        'name_desc'    => $projects->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE),
        default        => $projects->sortByDesc(fn($p) => $p->finished_at_calc ?? $p->updated_at),
    };

    // 5) Paginate Collection (LengthAwarePaginator)
    $perPage = 10;
    $current = (int) ($r->get('page', 1));
    $items = $projects instanceof Collection ? $projects : collect($projects);
    $paged = new LengthAwarePaginator(
        $items->forPage($current, $perPage)->values(),
        $items->count(),
        $perPage,
        $current,
        ['path' => $r->url(), 'query' => $r->query()]
    );

    return view('semua.arsip', ['projects' => $paged]);
})->name('semua.arsip');

Route::get('/dig/projects/{project}', [ProjectController::class, 'show'])
    ->name('dig.projects.show');


Route::post('/progresses/{progress}/confirm', [ProgressUpdateController::class, 'confirm'])
    ->name('progresses.confirm');

Route::post('/progresses/{progress}/updates', [ProgressUpdateController::class, 'storeUpdate'])
    ->name('progresses.updates.store');

Route::post('/progresses/{progress}/notes', [ProgressUpdateController::class, 'storeNote'])
    ->name('progresses.notes.store');


Route::prefix('dig')->name('dig.')->group(function () {
    Route::get('/projects/{project}', [ProjectController::class, 'show'])
         ->name('projects.show');
});


// Project & Progress
Route::resource('projects', ProjectController::class);
Route::post('/projects/{id}/progress',[ProgressController::class,'store'])->name('progress.store');
Route::get('/progress/{progress}/edit',[ProgressController::class,'edit'])->name('progress.edit');
Route::put('/progress/{progress}',[ProgressController::class,'update'])->name('progress.update');

Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::middleware(['auth'])->group(function () {
    // Tambah progress ke project
    Route::post('/projects/{project}/progresses', [ProgressController::class, 'store'])
        ->name('projects.progresses.store');

    // Update harian progress
    Route::post('/progresses/{progress}/updates', [ProgressUpdateController::class, 'store'])
        ->name('progresses.updates.store');

    // (opsional) edit & update
    Route::get('/progress-updates/{progressUpdate}/edit', [ProgressUpdateController::class, 'edit'])
        ->name('progress-updates.edit');
    Route::match(['put','patch'], '/progress-updates/{progressUpdate}', [ProgressUpdateController::class, 'update'])
        ->name('progress-updates.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/it/dashboard',  [DashboardController::class, 'itDashboard'])->name('it.dashboard');
    Route::get('/dig/dashboard', [DashboardController::class, 'digDashboard'])->name('dig.dashboard');
});

Route::resource('projects', ProjectController::class)->only(['edit','update','destroy']);

Route::get   ('/progresses/{progress}/edit',   [ProgressController::class, 'edit'])->name('progresses.edit');   // optional kalau mau halaman edit terpisah
Route::put   ('/progresses/{progress}',        [ProgressController::class, 'update'])->name('progresses.update');
Route::delete('/progresses/{progress}',        [ProgressController::class, 'destroy'])->name('progresses.destroy');
Route::get('/semua/progresses', [ProjectController::class,'progresses'])->name('semua.progresses');


Route::middleware('auth')->group(function () {
    Route::get('/dig/notifications', function () {
        $user  = auth()->user();
        $today = $user->notifications()
                      ->whereDate('created_at', now()->toDateString())
                      ->latest()
                      ->get();

        // unread count keseluruhan (di Blade kita hitung lagi yang sudah difilter)
        $unreadCount = $user->unreadNotifications()->count();

        return view('dig.notifications', compact('today', 'unreadCount'));
    })->name('dig.notifications');

    Route::post('/dig/notifications/read-all', function () {
        $u = auth()->user();
        $u->unreadNotifications->markAsRead();
        return back();
    })->name('dig.notifications.readAll');

    Route::post('/dig/notifications/{id}/read', function ($id) {
        $n = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        if (is_null($n->read_at)) $n->markAsRead();
        return back();
    })->name('dig.notifications.read');
});

// routes/web.php (contoh)
Route::get('/dig/notifications', [DigNotificationController::class, 'index'])->name('dig.notifications');
Route::post('/dig/notifications/read-all', [DigNotificationController::class, 'markAllRead'])->name('dig.notifications.readAll');
Route::post('/dig/notifications/{id}/read', [DigNotificationController::class, 'markRead'])->name('dig.notifications.read');

// DIG
Route::get('/dig/notifications', [\App\Http\Controllers\DigNotificationController::class, 'index'])
    ->name('dig.notifications');
Route::post('/dig/notifications/read/{id}', [\App\Http\Controllers\DigNotificationController::class, 'markRead'])
    ->name('dig.notifications.read');
Route::post('/dig/notifications/read-all', [\App\Http\Controllers\DigNotificationController::class, 'markAllRead'])
    ->name('dig.notifications.readAll');

// IT
Route::get('/it/notifications', [\App\Http\Controllers\DigNotificationController::class, 'itIndex'])
    ->name('it.notifications');
Route::post('/it/notifications/read/{id}', [\App\Http\Controllers\DigNotificationController::class, 'itMarkRead'])
    ->name('it.notifications.read');
Route::post('/it/notifications/read-all', [\App\Http\Controllers\DigNotificationController::class, 'itMarkAllRead'])
    ->name('it.notifications.readAll');

    
Route::middleware(['auth'])->group(function () {
    // tombol Memenuhi/Tidak Memenuhi (DIG)
    Route::patch('/projects/{project}/completion', [ProjectController::class, 'setCompletion'])
        ->name('projects.setCompletion');

    // Notifikasi IT
    Route::get('/it/notifications', [ItNotificationController::class, 'index'])
        ->name('it.notifications');
    Route::post('/it/notifications/read-all', [ItNotificationController::class, 'markAllRead'])
        ->name('it.notifications.readAll');
    Route::post('/it/notifications/{id}/read', [ItNotificationController::class, 'markRead'])
        ->name('it.notifications.read');
});

// routes/web.php
Route::get('/arsip', [\App\Http\Controllers\ArsipController::class, 'index'])
    ->name('semua.arsip');

Route::middleware(['auth'])->group(function () {
    Route::patch('/projects/{project}/completion', [ProjectController::class, 'setCompletion'])
        ->name('projects.setCompletion');
});

Route::middleware(['auth'])->group(function () {
    // Halaman Pengaturan Akun
    Route::get('/account/settings', [AccountController::class, 'edit'])
        ->name('account.setting');

    // Simpan perubahan
    Route::put('/account/settings', [AccountController::class, 'update'])
        ->name('account.update');
});



Route::middleware('auth')->group(function () {
    // IT
    Route::get('it/dashboard', [ItDashboard::class, 'index'])->name('it.dashboard');


    // Project (CRUD, finalisasi, dll.)
    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::patch('projects/{project}/completion', [ProjectController::class, 'setCompletion'])
        ->name('projects.setCompletion');

    // Halaman “Semua Progress”
    Route::get('progresses', [ProjectController::class, 'progresses'])->name('semua.progresses');
});
