<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    public const DAILY_RATE_RUPIAH = 25_000;

    public static function amountForOverdueDays(int $overdueDays): int
    {
        return max(0, $overdueDays) * self::DAILY_RATE_RUPIAH;
    }

    protected $fillable = [
        'loan_detail_id',
        'amount',
        'overdue_days',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'overdue_days' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function loanDetail(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class);
    }
}
