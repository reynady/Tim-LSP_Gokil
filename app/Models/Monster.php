<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monster extends Model
{
    protected $fillable = [
        'monster_code',
        'name',
        'description',
        'condition',
        'image',
        'threat_level',
        'origin_type',
        'status',
    ];

    public function imageUrl(): string
    {
        if ($this->image) {
            return filter_var($this->image, FILTER_VALIDATE_URL)
                ? $this->image
                : asset($this->image);
        }

        $imagePath = 'images/monsters/' . strtolower($this->monster_code) . '.svg';

        return asset(file_exists(public_path($imagePath))
            ? $imagePath
            : 'images/monsters/default.svg');
    }

    public function blockingLoanDetails(): HasMany
    {
        return $this->loanDetails()->whereNull('returned_at')
            ->whereHas('loan', fn (Builder $query) => $query->whereIn('status', Loan::STOCK_LOCKING_STATUSES));
    }

    public function canSchedule(): bool
    {
        return $this->status === 'available'
            || ($this->status === 'borrowed' && $this->blockingLoanDetails()->exists());
    }

    public function availableUnitsDuring(CarbonInterface $start, CarbonInterface $end): int
    {
        if (! $this->canSchedule()) {
            return 0;
        }

        $overlap = $this->blockingLoanDetails()->whereHas('loan', function (Builder $query) use ($start, $end) {
            $query->where(function (Builder $scheduled) use ($start, $end) {
                $scheduled->where('starts_at', '<', $end)->where('ends_at', '>', $start);
            })->orWhereNull('starts_at')->orWhereNull('ends_at');
        })->with('loan')->get()->contains(function (LoanDetail $detail) use ($start, $end) {
            // Legacy imports/seeders can still contain date-only loans.
            return $start->lt($detail->loan->scheduled_end) && $end->gt($detail->loan->scheduled_start);
        });

        return $overlap ? 0 : 1;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_monster'
        );
    }

    public function loanDetails(): HasMany
    {
        return $this->hasMany(LoanDetail::class);
    }
}