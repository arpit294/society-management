<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceBillStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! in_array(strtolower((string) $this->input('payment_method')), ['upi'], true)) {
            $this->merge(['transaction_id' => null]);

            return;
        }

        if ($this->has('transaction_id')) {
            $this->merge([
                'transaction_id' => trim((string) $this->input('transaction_id')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maintenanceBillId = $this->route('maintenanceBill');

        return [
            'status' => 'required|in:pending,paid,due,cancelled',
            'payment_method' => 'required_if:status,paid|nullable|in:cash,upi,CASH,UPI',
            'transaction_id' => [
                'nullable',
                'required_if:payment_method,upi,UPI',
                'digits:12',
                Rule::unique('maintenance_bills', 'transaction_id')->ignore($maintenanceBillId),
                Rule::unique('name_transfer_bills', 'transaction_id'),
                Rule::unique('prepaid_maintenances', 'transaction_id'),
            ],
            'payment_slip' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.required_if' => 'The UTR number is required for UPI payments.',
            'transaction_id.digits' => 'The UTR number must be exactly 12 digits.',
            'transaction_id.unique' => 'This UTR number has already been used.',
        ];
    }

    public function attributes(): array
    {
        return [
            'transaction_id' => 'UTR number',
        ];
    }
}
