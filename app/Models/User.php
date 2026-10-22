<?php

namespace App\Models;

use App\Enums\IdVerificationStatus;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'phone_number', 'profile_pic_url', 'country',
    'email_verification_code', 'email_verification_code_expires_at',
    'notify_bookings', 'notify_messages', 'notify_route_alerts',
])]
#[Hidden(['password', 'remember_token', 'email_verification_code', 'email_verification_code_expires_at'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'average_rating' => 'decimal:2',
            'total_reviews' => 'integer',
            'completed_deliveries_count' => 'integer',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'notify_bookings' => 'boolean',
            'notify_messages' => 'boolean',
            'notify_route_alerts' => 'boolean',
        ];
    }

    /**
     * Active within the last couple of minutes — long enough to tolerate
     * the app's own polling gaps, short enough that "online" still means
     * something.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->gt(now()->subMinutes(2));
    }

    /**
     * Generates a fresh 6-digit code (valid 15 minutes), replacing any
     * previous one, and returns it so the caller can email it.
     */
    public function generateEmailVerificationCode(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => now()->addMinutes(15),
        ])->save();

        return $code;
    }

    /**
     * Checks the given code against the stored one and its expiry. On a
     * match, marks the email verified and clears the code so it can't be
     * reused.
     */
    public function verifyEmailWithCode(string $code): bool
    {
        if ($this->email_verification_code === null || $this->email_verification_code_expires_at === null) {
            return false;
        }

        if ($this->email_verification_code_expires_at->isPast()) {
            return false;
        }

        if (! hash_equals($this->email_verification_code, $code)) {
            return false;
        }

        $this->forceFill([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->save();

        if (! $this->hasVerifiedEmail()) {
            $this->markEmailAsVerified();
        }

        return true;
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function goods(): HasMany
    {
        return $this->hasMany(Good::class);
    }

    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(Rating::class, 'ratee_id');
    }

    public function reportsFiled(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function blocksMade(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blocksReceived(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_user_id');
    }

    public function routeAlerts(): HasMany
    {
        return $this->hasMany(RouteAlert::class);
    }

    public function featureRequests(): HasMany
    {
        return $this->hasMany(FeatureRequest::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function idVerifications(): HasMany
    {
        return $this->hasMany(IdVerification::class);
    }

    /**
     * The most recently submitted ID verification, if any — used to show
     * the user's current status (none, pending, approved, rejected).
     */
    public function latestIdVerification(): ?IdVerification
    {
        return $this->idVerifications()->latest()->first();
    }

    public function isIdVerified(): bool
    {
        return $this->latestIdVerification()?->status === IdVerificationStatus::Approved;
    }

    /**
     * Bans the user and immediately revokes every mobile session token, so
     * a ban takes effect instantly rather than waiting for the token to
     * next be checked.
     */
    public function ban(): void
    {
        $this->forceFill(['is_banned' => true, 'banned_at' => now()])->save();
        $this->tokens()->delete();
    }

    public function unban(): void
    {
        $this->forceFill(['is_banned' => false, 'banned_at' => null])->save();
    }

    /**
     * IDs of users this user has blocked, or who have blocked this user —
     * either direction hides the pair from each other's feeds.
     *
     * @return array<int, int>
     */
    public function blockedUserIds(): array
    {
        return $this->blocksMade()->pluck('blocked_user_id')
            ->merge($this->blocksReceived()->pluck('blocker_id'))
            ->unique()
            ->values()
            ->all();
    }

    public function hasBlockedOrIsBlockedBy(int $userId): bool
    {
        return in_array($userId, $this->blockedUserIds(), true);
    }
}
