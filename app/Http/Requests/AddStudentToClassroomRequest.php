<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddStudentToClassroomRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Data siswa tidak boleh kosong',
            'student_ids.array' => 'Data siswa harus berupa array',
            'student_ids.min' => 'Minimal 1 siswa harus dipilih',
            'student_ids.*.required' => 'ID siswa tidak boleh kosong',
            'student_ids.*.exists' => 'Siswa tidak ditemukan',
        ];
    }
}