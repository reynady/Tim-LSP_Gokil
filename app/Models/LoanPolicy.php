<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPolicy extends Model
{
    public const DEFAULT_ACTIVE_LOAN_LIMIT = 2;

    public const DEFAULT_MAX_DURATION_DAYS = 5;

    protected $fillable = [
        'max_active_loans',
        'max_duration_days',
    ];

    public static function current(): self
    {
        return static::query()->findOrFail(1);
    }
}
