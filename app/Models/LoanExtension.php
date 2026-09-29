<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanExtension extends Model
{
    protected $fillable = [
        'loan_id',
        'tgl_lama',
        'tgl_baru',
        'alasan',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'tgl_lama' => 'date',
        'tgl_baru' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
