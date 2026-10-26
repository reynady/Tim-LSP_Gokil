<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function processedReturns(): HasMany
    {
        return $this->hasMany(Loan::class, 'returned_by');
    }

    public const INACTIVE_MESSAGE = 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.';

    public function revokeSessions(): void
    {
        $this->setRememberToken(null);
        $this->save();

        if (config('session.driver') !== 'database') {
            return;
        }

        $sessions = DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $this->id);

        // Retain only a temporary revocation marker so deleted sessions can display the reason.
        foreach ((clone $sessions)->pluck('id') as $sessionId) {
            Cache::put(
                'revoked-session:'.hash('sha256', $sessionId),
                true,
                now()->addMinutes((int) config('session.lifetime', 120) + 5)
            );
        }

        $sessions->delete();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
