<?php

namespace App\Http\Requests\Operator;

use App\Enums\DayEnum;
use App\Http\Requests\ApiRequest;

class UpdateLessonSchedulesRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $scheduleId = $this->route('lesson_schedule')?->id ?? $this->route('id');

        return [
            'classroom_id' => 'sometimes|required|exists:classrooms,id',
            'day' => 'sometimes|required|in:' . implode(',', DayEnum::values()),
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'teacher_id' => 'sometimes|required|exists:employees,id', 
            'lesson_hour_id' => 'sometimes|required|exists:lesson_hours,id',
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Kelas wajib dipilih.',
            'classroom_id.exists' => 'Kelas tidak ditemukan.',
            'day.required' => 'Hari wajib dipilih.',
            'day.in' => 'Hari tidak valid.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'lesson_hour_id.required' => 'Jam pelajaran wajib dipilih.',
            'lesson_hour_id.exists' => 'Jam pelajaran tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'classroom_id' => 'kelas',
            'day' => 'hari',
            'subject_id' => 'mata pelajaran',
            'teacher_id' => 'guru pengajar',
            'lesson_hour_id' => 'jam pelajaran',
        ];
    }
}