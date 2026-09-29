<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'kode_aset',
        'nama',
        'category_id',
        'merk',
        'foto',
        'jumlah_total',
        'jumlah_tersedia',
        'kondisi',
        'status',
        'deskripsi',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function isAvailable(): bool
    {
        return $this->jumlah_tersedia > 0 && $this->status === 'Tersedia';
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('status', 'Tersedia')->where('jumlah_tersedia', '>', 0);
    }
}
