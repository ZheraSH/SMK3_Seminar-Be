<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

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
            'student_ids.*' => [
                'required',
                'exists:students,id',
                function ($attribute, $value, $fail) {
                    $exists = \Illuminate\Support\Facades\DB::table('classroom_students')
                        ->where('student_id', $value)
                        ->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Siswa dengan ID ' . $value . ' sudah terdaftar di kelas lain yang aktif.');
                    }
                }
            ],
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

    public function attributes(): array
    {
        return [
            'student_ids' => 'data siswa',
            'student_ids.*' => 'ID siswa',
        ];
    }
}
