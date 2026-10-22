<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\PickupMethod;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'from_city',
    'to_city',
    'departure_date_time',
    'airline_name',
    'max_weight_kg',
    'rate_per_kg',
    'currency_type',
    'terms_accepted_at',
    'photo_url',
    'delivery_method',
    'pickup_method',
])]
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departure_date_time' => 'datetime',
            'max_weight_kg' => 'decimal:2',
            'rate_per_kg' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'delivery_method' => DeliveryMethod::class,
            'pickup_method' => PickupMethod::class,
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
