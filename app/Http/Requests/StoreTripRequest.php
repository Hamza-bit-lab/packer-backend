<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Trip::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_city' => ['required', 'string', 'max:255'],
            'to_city' => ['required', 'string', 'max:255'],
            'departure_date_time' => ['required', 'date', 'after:now'],
            'airline_name' => ['required', 'string', 'max:255'],
            'max_weight_kg' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'rate_per_kg' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'currency_type' => ['required', 'string', 'size:3'],
            'delivery_method' => ['required', 'string', 'in:receiver_pickup,traveler_delivers'],
            'pickup_method' => ['required', 'string', 'in:traveler_picks_up,sender_delivers'],
            'terms_accepted' => ['required', 'accepted'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
