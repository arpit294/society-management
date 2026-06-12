<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceBillStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,paid,due,cancelled',
            'payment_method' => 'required_if:status,paid|nullable|in:cash,upi,CASH,UPI',
            'transaction_id' => 'nullable|string',
            'payment_slip' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
