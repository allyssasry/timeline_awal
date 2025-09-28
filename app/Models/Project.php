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

    // Opsional: supaya status & flag helper ikut muncul saat ->toArray() / JSON
    protected $appends = [
        'status_text',
        'status_color',
        'can_decide_completion',
        // 'desired_average_percent', 'is_finished', 'finished_at_calc' // kalau mau ikut juga
    ];

    /* ================== RELATIONSHIPS ================== */

    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    // Jika Anda memang punya tabel progress_updates langsung di level project, biarkan;
    // kalau tidak perlu, hapus method ini.
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

    // Rata-rata target awal dari semua progress (dibulatkan)
    public function getDesiredAveragePercentAttribute(): int
    {
        // gunakan collection saat relasi sudah diload (menghindari query ulang)
        if ($this->relationLoaded('progresses')) {
            $avg = (float) ($this->progresses->avg('desired_percent') ?? 0);
            return (int) round($avg);
        }

        // fallback ke query bila belum diload
        return (int) round($this->progresses()->avg('desired_percent') ?? 0);
    }

    // Apakah seluruh progress sudah mencapai target & terkonfirmasi
    public function getIsFinishedAttribute(): bool
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        if ($this->progresses->isEmpty()) {
            return false;
        }

        foreach ($this->progresses as $pr) {
            // updates sudah diurut desc; ambil terbaru
            $latest = $pr->updates->first();
            $real   = (int) ($latest->progress_percent ?? $latest->percent ?? 0);

            if (is_null($pr->confirmed_at) || $real < (int) $pr->desired_percent) {
                return false;
            }
        }

        return true;
    }

    // Tanggal selesai dihitung dari konfirmasi terakhir progress
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
        // Jika belum pernah diputuskan (completed_at null atau meets_requirement null)
        if (!$this->completed_at || is_null($this->meets_requirement)) {
            // fallback: kalau sebenarnya semua progress selesai namun belum difinalisasi
            return $this->is_finished ? 'Project Selesai' : 'Dalam Proses';
        }

        // Sudah diputuskan
        return $this->meets_requirement
            ? 'Project Selesai, Memenuhi'
            : 'Project Selesai, Tidak Memenuhi';
    }

    public function getStatusColorAttribute(): string
    {
        // Belum final: merah tema
        if (!$this->completed_at || is_null($this->meets_requirement)) {
            return '#7A1C1C';
        }

        // Sudah final: hijau bila memenuhi, merah bila tidak
        return $this->meets_requirement ? '#166534' : '#7A1C1C';
    }

    /**
     * BOLEH ambil keputusan penyelesaian?
     * - Semua progress sudah dikonfirmasi & meeting target
     * - DAN project belum diputuskan (meets_requirement === null)
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

        return $allMetAndConfirmed && is_null($this->meets_requirement);
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
}
