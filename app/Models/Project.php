<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'title',
        'digital_banking_id',
        'developer_id',
        // opsional kalau mau mass-assign:
        'completed_at',
        'meets_requirement',
    ];

    /** Casts untuk field status penyelesaian */
    protected $casts = [
        'completed_at'      => 'datetime',
        'meets_requirement' => 'boolean',
    ];

    /* ================== RELATIONSHIPS ================== */
    public function progresses()
    {
        return $this->hasMany(\App\Models\Progress::class);
    }

    public function updates()
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function digitalBanking()
    {
        return $this->belongsTo(User::class, 'digital_banking_id');
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ================== COMPUTED / HELPERS ================== */

    // Rata-rata target awal dari semua progress (dibulatkan)
    public function getDesiredAveragePercentAttribute(): int
    {
        return (int) round($this->progresses()->avg('desired_percent') ?? 0);
    }

    // Apakah seluruh progress sudah mencapai target & terkonfirmasi
    public function getIsFinishedAttribute(): bool
    {
        if (!$this->relationLoaded('progresses')) {
            $this->load(['progresses.updates' => fn($q) => $q->orderByDesc('update_date')]);
        }

        if ($this->progresses->isEmpty()) return false;

        foreach ($this->progresses as $pr) {
            $latest = $pr->updates->first(); // sudah di-sort desc
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

        $dates = $this->progresses
            ->pluck('confirmed_at')
            ->filter();

        return $dates->isNotEmpty() ? $dates->max() : null;
    }

    /** ====== STATUS UNTUK UI (DIG & IT) ====== */
    public function getStatusTextAttribute(): string
    {
        if (!$this->completed_at) {
            // fallback: jika semua progress selesai tapi belum ditekan tombol finalisasi
            return $this->is_finished ? 'Project Selesai' : 'Dalam Proses';
        }

        if ($this->meets_requirement === true)  return 'Project Selesai, Memenuhi';
        if ($this->meets_requirement === false) return 'Project Selesai, Tidak Memenuhi';

        return 'Project Selesai';
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->completed_at) return '#7A1C1C';                // merah gelap (proses)
        return $this->meets_requirement ? '#166534' : '#7A1C1C';   // hijau jika memenuhi
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
