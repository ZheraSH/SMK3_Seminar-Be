<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;
use App\Models\Major;
use App\Models\LevelClass;
use Illuminate\Support\Facades\DB;

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
                'max:2',
                function ($attribute, $value, $fail) {
                    $major = Major::find($this->major_id);
                    $levelClass = LevelClass::find($this->level_class_id);

                    if (! $major || ! $levelClass) {
                        return;
                    }

                    $number = trim($value);

                    $slug = strtoupper(sprintf(
                        '%s-%s-%s',
                        $levelClass->name,
                        $major->code,
                        $number
                    ));

                    $exists = DB::table('classrooms')
                        ->where('slug', $slug)
                        ->where('major_id', $this->major_id)
                        ->where('level_class_id', $this->level_class_id)
                        ->where('school_year_id', $this->school_year_id)
                        ->exists();

                    if ($exists) {
                        $fail('Nama kelas ini sudah ada untuk kombinasi jurusan, tingkatan, dan tahun ajaran yang sama');
                    }
                }
            ],
            'major_id' => 'required|exists:majors,id',
            'level_class_id' => 'required|exists:level_classes,id',
            'school_year_id' => 'required|exists:school_years,id',
            'homeroom_teacher_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('classrooms')
                        ->where('homeroom_teacher_id', $value)
                        ->where('school_year_id', $this->school_year_id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail('Guru ini sudah menjadi wali kelas di kelas lain pada tahun ajaran ini.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas tidak boleh kosong',
            'name.string' => 'Nama kelas harus berupa teks',
            'name.max' => 'Nama kelas maksimal 2 karakter',
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
