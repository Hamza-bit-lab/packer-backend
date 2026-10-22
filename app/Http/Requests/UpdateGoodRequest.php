<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('good'));
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
            'item_description' => ['sometimes', 'string', 'max:1000'],
            'weight_kg' => ['sometimes', 'numeric', 'min:0.01', 'max:1000'],
            'offered_reward' => ['sometimes', 'numeric', 'min:0', 'max:1000000'],
            'currency_type' => ['sometimes', 'string', 'size:3'],
            'pickup_preference' => ['sometimes', 'string', 'in:traveler_picks_up,sender_delivers'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
