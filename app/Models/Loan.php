<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $table = 'loans';

    protected $fillable = [
        'kode_pinjam',
        'user_id',
        'nama_peminjam',
        'email',
        'no_hp',
        'department_id',
        'tgl_pinjam',
        'tgl_rencana_kembali',
        'tgl_kembali_aktual',
        'tujuan',
        'status',
        'is_long_term',
        'is_walkin',
        'approved_by',
        'rejection_reason',
        'handover_kondisi',
        'handover_catatan',
        'handover_foto',
        'handover_at',
        'return_kondisi',
        'return_catatan',
        'return_foto',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_rencana_kembali' => 'date',
        'tgl_kembali_aktual' => 'date',
        'handover_at' => 'datetime',
        'is_long_term' => 'boolean',
        'is_walkin' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(LoanExtension::class);
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, ['dikembalikan', 'dikembalikan_terlambat', 'ditolak', 'dibatalkan', 'diajukan'])) {
            return false;
        }

        if ($this->tgl_kembali_aktual) {
            return Carbon::parse($this->tgl_kembali_aktual)->gt(Carbon::parse($this->tgl_rencana_kembali));
        }

        return Carbon::parse($this->tgl_rencana_kembali)->lt(Carbon::today());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'diajukan' => 'Diajukan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            'dipinjam' => 'Dipinjam',
            'terlambat' => 'Terlambat',
            'dikembalikan' => 'Dikembalikan',
            'dikembalikan_terlambat' => 'Dikembalikan Terlambat',
            'hilang' => 'Hilang',
            default => ucfirst((string) $this->status),
        };
    }
}
