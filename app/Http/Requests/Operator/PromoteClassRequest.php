<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;
use App\Models\Employee;

class PromoteClassRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'homeroom_teacher_id' => [
                'required', 
                'uuid', 
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    $employee = Employee::find($value);
                    if ($employee && !$employee->user->hasRole('homeroom_teacher')) {
                        $fail('Wali kelas yang dipilih tidak memiliki hak akses sebagai wali kelas.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'homeroom_teacher_id.required' => 'Wali kelas wajib dipilih saat menaikkan kelas.',
            'homeroom_teacher_id.uuid' => 'Format ID wali kelas tidak valid.',
            'homeroom_teacher_id.exists' => 'Wali kelas tidak ditemukan.',
        ];
    }
}
