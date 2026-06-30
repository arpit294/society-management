<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Late penalty settings
            'penalty_monthly_value' => 'nullable|numeric|min:0|max:100',
            'penalty_quarterly_value' => 'nullable|numeric|min:0|max:100',
            'penalty_half_yearly_value' => 'nullable|numeric|min:0|max:100',
            'penalty_yearly_value' => 'nullable|numeric|min:0|max:100',

            // Prepayment discount settings
            'discount_monthly_value' => 'nullable|numeric|min:0|max:100',
            'discount_quarterly_value' => 'nullable|numeric|min:0|max:100',
            'discount_half_yearly_value' => 'nullable|numeric|min:0|max:100',
            'discount_yearly_value' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            '*.max' => 'Percentage values must be between 0 and 100.',
            '*.min' => 'Percentage values cannot be negative.',
        ];
    }
}
