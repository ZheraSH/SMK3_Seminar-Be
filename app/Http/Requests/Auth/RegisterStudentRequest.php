<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female',
            'nisn' => 'required|unique:students,nisn',
            'religion_id' => 'nullable|exists:religions,id',
            'birthdate' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'no_kk' => 'nullable|string|max:255',
            'no_birth_certificate' => 'nullable|string|max:255',
            'order_child' => 'nullable|integer|min:1',
            'count_siblings' => 'nullable|integer|min:0',
            'point' => 'nullable|integer|min:0',
        ];
    }
}


