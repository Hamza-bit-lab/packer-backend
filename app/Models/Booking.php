<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\Payer;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['trip_id', 'good_id', 'sender_id', 'traveler_id', 'status', 'estimated_cost', 'payer', 'inspection_confirmed_at', 'delivery_proof_photo_url'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'estimated_cost' => 'decimal:2',
            'payer' => Payer::class,
            'inspection_confirmed_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function good(): BelongsTo
    {
        return $this->belongsTo(Good::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function accept(): void
    {
        $this->update(['status' => BookingStatus::Accepted]);
    }

    public function reject(): void
    {
        $this->update(['status' => BookingStatus::Rejected]);
    }

    public function cancel(): void
    {
        $this->update(['status' => BookingStatus::Cancelled]);
    }

    public function markInTransit(): void
    {
        $this->update(['status' => BookingStatus::InTransit]);
    }

    public function markArrived(): void
    {
        $this->update(['status' => BookingStatus::ArrivedAtDestination]);
    }

    public function complete(?string $deliveryProofPhotoUrl = null): void
    {
        $this->update([
            'status' => BookingStatus::Completed,
            'inspection_confirmed_at' => now(),
            'delivery_proof_photo_url' => $deliveryProofPhotoUrl,
        ]);
    }
}
