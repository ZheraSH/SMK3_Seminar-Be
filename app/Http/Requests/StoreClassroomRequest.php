<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreClassroomRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classrooms')->where(function ($query) {
                    return $query->where('major_id', $this->major_id)
                                 ->where('level_class_id', $this->level_class_id)
                                 ->where('school_year_id', $this->school_year_id);
                })
            ],
            'major_id' => 'required|exists:majors,id',
            'level_class_id' => 'required|exists:level_classes,id',
            'school_year_id' => 'required|exists:school_years,id',
            'homeroom_teacher_id' => 'required|exists:employees,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas tidak boleh kosong',
            'name.unique' => 'Nama kelas ini sudah ada untuk kombinasi jurusan, tingkatan, dan tahun ajaran yang sama',
            'major_id.required' => 'Jurusan tidak boleh kosong',
            'major_id.exists' => 'Jurusan tidak ditemukan',
            'level_class_id.required' => 'Tingkatan kelas tidak boleh kosong',
            'level_class_id.exists' => 'Tingkatan kelas tidak ditemukan',
            'school_year_id.required' => 'Tahun ajaran tidak boleh kosong',
            'school_year_id.exists' => 'Tahun ajaran tidak ditemukan',
            'homeroom_teacher_id.required' => 'Guru/Wali tidak boleh kosong',
            'homeroom_teacher_id.exists' => 'Guru/Wali kelas tidak ditemukan',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama kelas',
            'major_id' => 'jurusan',
            'level_class_id' => 'tingkatan kelas',
            'school_year_id' => 'tahun ajaran',
            'homeroom_teacher_id' => 'guru/wali kelas',
        ];
    }
}