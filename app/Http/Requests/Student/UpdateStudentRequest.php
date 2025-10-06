<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $studentId = $this->route('id') ?? $this->route('student');

        return [
            'nisn' => 'sometimes|unique:students,nisn,' . $studentId,
            'religion_id' => 'sometimes|nullable|exists:religions,id',
            'birthdate' => 'sometimes|nullable|date',
            'birthplace' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'nik' => 'sometimes|nullable|string|max:255',
            'no_kk' => 'sometimes|nullable|string|max:255',
            'no_birth_certificate' => 'sometimes|nullable|string|max:255',
            'order_child' => 'sometimes|nullable|integer|min:1',
            'count_siblings' => 'sometimes|nullable|integer|min:0',
            'point' => 'sometimes|nullable|integer|min:0',
            'class' => 'sometimes|nullable|string|max:50',
            'major' => 'sometimes|nullable|string|max:100',
        ];
    }
}


