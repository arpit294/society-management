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
        $isFixedPenalty = ($this->input('penalty_type') === 'fixed');
        $isFixedDiscount = ($this->input('discount_type') === 'fixed');

        $penaltyRules = ['nullable', 'numeric', 'min:0'];
        if (!$isFixedPenalty) {
            $penaltyRules[] = 'max:100';
        }

        $discountRules = ['nullable', 'numeric', 'min:0'];
        if (!$isFixedDiscount) {
            $discountRules[] = 'max:100';
        }

        return [
            // Late penalty settings
            'penalty_monthly_value' => $penaltyRules,
            'penalty_quarterly_value' => $penaltyRules,
            'penalty_half_yearly_value' => $penaltyRules,
            'penalty_yearly_value' => $penaltyRules,

            // Prepayment discount settings
            'discount_quarterly_value' => $discountRules,
            'discount_half_yearly_value' => $discountRules,
            'discount_yearly_value' => $discountRules,
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
