<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class ChangePasswordRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak sesuai',
        ];
    }
}