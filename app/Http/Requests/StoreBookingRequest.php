<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'good_id' => ['required', 'integer', 'exists:goods,id'],
            'payer' => ['nullable', 'string', 'in:sender,receiver'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $good = Good::find($this->input('good_id'));
            $trip = Trip::find($this->input('trip_id'));

            if (! $good || ! $trip) {
                return;
            }

            // Either party can start the request: the sender browsing Find
            // Travelers, or the traveler browsing Find Goods — as long as
            // the requester actually owns one side of the pair.
            $isSender = $good->user_id === $this->user()->id;
            $isTraveler = $trip->user_id === $this->user()->id;

            if (! $isSender && ! $isTraveler) {
                $validator->errors()->add('good_id', 'You can only create a booking between your own item and your own trip.');

                return;
            }

            if ($good->user_id === $trip->user_id) {
                $validator->errors()->add('trip_id', 'You cannot book your own trip.');

                return;
            }

            $activeStatuses = [BookingStatus::Pending, BookingStatus::Accepted];

            if ($good->bookings()->whereIn('status', $activeStatuses)->exists()) {
                $validator->errors()->add('good_id', 'This item already has an active booking.');
            }

            $bookedWeight = $trip->bookings()
                ->whereIn('status', $activeStatuses)
                ->with('good')
                ->get()
                ->sum(fn (Booking $booking) => (float) $booking->good->weight_kg);

            if ($bookedWeight + (float) $good->weight_kg > (float) $trip->max_weight_kg) {
                $validator->errors()->add('good_id', 'This trip does not have enough remaining luggage capacity.');
            }
        });
    }
}
