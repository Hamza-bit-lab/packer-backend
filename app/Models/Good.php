<?php

namespace App\Models;

use App\Enums\PickupMethod;
use Database\Factories\GoodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'from_city',
    'to_city',
    'item_description',
    'receiver_email',
    'weight_kg',
    'offered_reward',
    'currency_type',
    'terms_accepted_at',
    'photo_url',
    'pickup_preference',
])]
class Good extends Model
{
    /** @use HasFactory<GoodFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'offered_reward' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'pickup_preference' => PickupMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
