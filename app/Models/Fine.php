<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    public const GRACE_PERIOD_DAYS = 5;

    public const DAILY_RATE_ETHER = 25;

    public static function amountForOverdueDays(int $overdueDays): int
    {
        return max(0, $overdueDays - self::GRACE_PERIOD_DAYS) * self::DAILY_RATE_ETHER;
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