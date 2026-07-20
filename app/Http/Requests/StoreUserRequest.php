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
        return $this->user()?->can('user_create') ?? false;
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
            'phone' => 'required|digits:10',
            'role' => [
                'required',
                'exists:roles,name',
            ],
            'designation' => 'nullable|string|max:100',
            'password' => 'required|string|min:6',
            'aadhar_id' => [
                'nullable',
                'string',
                'max:25',
                Rule::unique('users', 'aadhar_id')->whereNotNull('aadhar_id'),
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }
}
