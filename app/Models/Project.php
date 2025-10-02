<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'title',
        'digital_banking_id',
        'developer_id',
        'completed_at',
        'meets_requirement',
    ];

    protected $casts = [
        'completed_at'      => 'datetime',
        'meets_requirement' => 'boolean',
    ];

    protected $appends = [
        'status_text',
        'status_color',
        'can_decide_completion',
        'ready_because_overdue', // NEW
    ];

    /* ================== RELATIONSHIPS ================== */

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function digitalBanking(): BelongsTo
    {
        return $this->belongsTo(User::class, 'digital_banking_id');
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ================== COMPUTED / HELPERS ================== */

    public function getDesiredAveragePercentAttribute(): int
    {
        if ($this->relationLoaded('progresses')) {
            $avg = (float) ($this->progresses->avg('desired_percent') ?? 0);
            return (int) round($avg);
        }
        return (int) round($this->progresses()->avg('desired_percent') ?? 0);
    }

    public function getIsFinishedAttribute(): bool
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        if ($this->progresses->isEmpty()) {
            return false;
        }

        foreach ($this->progresses as $pr) {
            $latest = $pr->updates->first();
            $real   = (int) ($latest->progress_percent ?? $latest->percent ?? 0);

            if (is_null($pr->confirmed_at) || $real < (int) $pr->desired_percent) {
                return false;
            }
        }

        return true;
    }

    public function getFinishedAtCalcAttribute()
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load('progresses');
        }

        $dates = $this->progresses->pluck('confirmed_at')->filter();
        return $dates->isNotEmpty() ? $dates->max() : null;
    }

    /** ====== STATUS UNTUK UI (DIG & IT) ====== */
    public function getStatusTextAttribute(): string
    {
        if (!$this->completed_at || is_null($this->meets_requirement)) {
            return $this->is_finished ? 'Project Selesai' : 'Dalam Proses';
        }

        return $this->meets_requirement
            ? 'Project Selesai, Memenuhi'
            : 'Project Selesai, Tidak Memenuhi';
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->completed_at || is_null($this->meets_requirement)) {
            return '#7A1C1C';
        }
        return $this->meets_requirement ? '#166534' : '#7A1C1C';
    }

    /**
     * Skenario tambahan untuk finalisasi:
     * - ADA minimal 1 progress yang SUDAH LEWAT timeline SELESAI,
     *   BELUM dikonfirmasi, dan realisasi < target (unmet-overdue)
     * - Progress lainnya SUDAH dikonfirmasi.
     */
    public function readyBecauseOverdue(): bool // NEW
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        $hasOverdueUnmet = false;

        foreach ($this->progresses as $pr) {
            $latest = $pr->updates->first();
            $real   = $latest ? (int)($latest->progress_percent ?? $latest->percent ?? 0) : 0;

            $end   = $pr->end_date ? \Illuminate\Support\Carbon::parse($pr->end_date)->startOfDay() : null;
            $od    = $end ? $end->lt(now()->startOfDay()) : false;
            $unmet = $od && is_null($pr->confirmed_at) && ($real < (int)$pr->desired_percent);

            if ($unmet) {
                $hasOverdueUnmet = true;
                break;
            }
        }

        if (!$hasOverdueUnmet) {
            return false;
        }

        $othersAllConfirmed = $this->progresses->every(function ($pr) {
            $latest = $pr->updates->first();
            $real   = $latest ? (int)($latest->progress_percent ?? $latest->percent ?? 0) : 0;

            $end   = $pr->end_date ? \Illuminate\Support\Carbon::parse($pr->end_date)->startOfDay() : null;
            $od    = $end ? $end->lt(now()->startOfDay()) : false;
            $unmet = $od && is_null($pr->confirmed_at) && ($real < (int)$pr->desired_percent);

            return $unmet ? true : !is_null($pr->confirmed_at);
        });

        return $othersAllConfirmed && is_null($this->meets_requirement);
    }

    public function getReadyBecauseOverdueAttribute(): bool // NEW
    {
        return $this->readyBecauseOverdue(); // NEW
    }

    /**
     * BOLEH ambil keputusan penyelesaian?
     */
    public function getCanDecideCompletionAttribute(): bool
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        if ($this->progresses->isEmpty()) {
            return false;
        }

        $allMetAndConfirmed = $this->progresses->every(function ($pr) {
            $latest = $pr->updates->first();
            $real   = (int) ($latest->progress_percent ?? $latest->percent ?? 0);
            return $real >= (int) $pr->desired_percent && !is_null($pr->confirmed_at);
        });

        $readyBecauseOverdue = $this->readyBecauseOverdue(); // NEW

        return ($allMetAndConfirmed || $readyBecauseOverdue) && is_null($this->meets_requirement);
    }

    /* ================== SCOPES OPSIONAL ================== */

    public function scopeCompleted($q)
    {
        return $q->whereNotNull('completed_at');
    }

    public function scopeNotCompleted($q)
    {
        return $q->whereNull('completed_at');
    }

    public function scopeMeets($q)
    {
        return $q->where('meets_requirement', true);
    }

    public function scopeNotMeets($q)
    {
        return $q->where('meets_requirement', false);
    }

    /** ================== SCOPES TAMBAHAN (BARU) ================== */

    /**
     * Proyek sudah DIFINALISASI (memenuhi/tidak memenuhi).
     */
    public function scopeFinalized($q)
    {
        return $q->where(function ($x) {
            $x->whereNotNull('completed_at')
              ->orWhereNotNull('meets_requirement');
        });
    }

    /**
     * Semua progress SUDAH dikonfirmasi (dan minimal ada 1 progress).
     */
    public function scopeAllProgressConfirmed($q)
    {
        return $q->whereHas('progresses')
                 ->whereDoesntHave('progresses', function ($qq) {
                     $qq->whereNull('confirmed_at');
                 });
    }

    /**
     * Ada progress yang BELUM dikonfirmasi atau belum punya progress sama sekali.
     */
    public function scopeHasUnconfirmedOrNoProgress($q)
    {
        return $q->doesntHave('progresses')
                 ->orWhereHas('progresses', function ($qq) {
                     $qq->whereNull('confirmed_at');
                 });
    }

    /** (opsional) akses cepat sebagai properti: $project->is_finalized */
    public function getIsFinalizedAttribute(): bool
    {
        return !is_null($this->completed_at) || !is_null($this->meets_requirement);
    }
}
