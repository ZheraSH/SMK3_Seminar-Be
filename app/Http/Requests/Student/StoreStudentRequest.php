<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama' => 'required|string|max:255',
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
            'kelas' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:100',
        ];
    }
}


