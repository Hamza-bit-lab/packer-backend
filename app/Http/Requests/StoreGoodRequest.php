<?php

namespace App\Http\Requests;

use App\Models\Good;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Good::class);
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
            'item_description' => ['required', 'string', 'max:1000'],
            'weight_kg' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'offered_reward' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'currency_type' => ['required', 'string', 'size:3'],
            'pickup_preference' => ['required', 'string', 'in:traveler_picks_up,sender_delivers'],
            'terms_accepted' => ['required', 'accepted'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
