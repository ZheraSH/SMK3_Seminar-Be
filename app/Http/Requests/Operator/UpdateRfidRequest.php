<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class UpdateRfidRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih',
            'student_id.exists'   => 'Siswa yang dipilih tidak valid',
        ];
    }
}
