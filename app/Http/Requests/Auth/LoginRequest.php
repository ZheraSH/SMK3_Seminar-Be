<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:100'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.max' => 'Password maksimal 100 karakter.',
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
        ]);
    }
}