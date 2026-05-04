<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class PromoteClassRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_classroom_id' => 'required|exists:classrooms,id|different:classroomId',
        ];
    }

    public function messages(): array
    {
        return [
            'new_classroom_id.required' => 'Kelas tujuan wajib dipilih',
            'new_classroom_id.exists' => 'Kelas tujuan tidak ditemukan',
            'new_classroom_id.different' => 'Kelas tujuan harus berbeda dari kelas saat ini',
        ];
    }
}
