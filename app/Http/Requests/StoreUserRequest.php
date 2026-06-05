<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:10',
            'role' => [
                'required',
                Rule::in(['owner', 'rental', 'security', 'committee_member']),
            ],
            'password' => 'required|string|min:6',
            'aadhar_id' => 'required|string|max:20',
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }
}
