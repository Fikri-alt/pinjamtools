<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageCase extends Model
{
    protected $fillable = [
        'item_id',
        'loan_id',
        'deskripsi',
        'status',
        'biaya',
        'tindak_lanjut',
    ];

    protected $casts = [
        'biaya' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
