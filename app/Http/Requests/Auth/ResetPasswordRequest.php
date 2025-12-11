<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class ResetPasswordRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->hasRole('school_operator');
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'User dengan email tersebut tidak ditemukan',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak sesuai',
        ];
    }
}