<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    public const STOCK_LOCKING_STATUSES = ['active', 'borrowed', 'approved', 'pending'];
    protected $fillable = [
        'loan_code',
        'user_id',
        'returned_by',
        'loan_date',
        'due_date',
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    public function getScheduledStartAttribute(): CarbonImmutable
    {
        return $this->starts_at ?? CarbonImmutable::instance($this->loan_date)->startOfDay();
    }

    public function getScheduledEndAttribute(): CarbonImmutable
    {
        return $this->ends_at ?? CarbonImmutable::instance($this->due_date)->addDay()->startOfDay();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LoanDetail::class);
    }
}