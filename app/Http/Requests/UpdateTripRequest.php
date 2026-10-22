<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('trip'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_city' => ['sometimes', 'string', 'max:255'],
            'to_city' => ['sometimes', 'string', 'max:255'],
            'departure_date_time' => ['sometimes', 'date', 'after:now'],
            'airline_name' => ['sometimes', 'string', 'max:255'],
            'max_weight_kg' => ['sometimes', 'numeric', 'min:0.1', 'max:1000'],
            'rate_per_kg' => ['sometimes', 'numeric', 'min:0.01', 'max:100000'],
            'currency_type' => ['sometimes', 'string', 'size:3'],
            'delivery_method' => ['sometimes', 'string', 'in:receiver_pickup,traveler_delivers'],
            'pickup_method' => ['sometimes', 'string', 'in:traveler_picks_up,sender_delivers'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
