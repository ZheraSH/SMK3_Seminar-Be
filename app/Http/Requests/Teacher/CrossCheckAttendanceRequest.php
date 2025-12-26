<?php

namespace App\Http\Requests\Teacher;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\ApiRequest;

class CrossCheckAttendanceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'lesson_schedule_id' => 'required|exists:lesson_schedules,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date|date_format:Y-m-d',
            'lesson_order' => 'required|integer|min:2',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => [
                'required',
                'in:' . implode(',', [
                    AttendanceStatusEnum::PRESENT->value,
                    AttendanceStatusEnum::LATE->value,
                    AttendanceStatusEnum::LEAVE->value,
                    AttendanceStatusEnum::SICK->value,
                    AttendanceStatusEnum::ALPHA->value,
                ])
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Kelas wajib dipilih',
            'classroom_id.exists' => 'Kelas tidak ditemukan',
            'lesson_schedule_id.required' => 'Jadwal pelajaran wajib dipilih',
            'lesson_schedule_id.exists' => 'Jadwal pelajaran tidak ditemukan',
            'subject_id.required' => 'Mata pelajaran wajib dipilih',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan',
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.date_format' => 'Format tanggal harus Y-m-d',
            'lesson_order.required' => 'Urutan jam pelajaran wajib diisi',
            'lesson_order.min' => 'Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya',
            'attendances.required' => 'Data kehadiran siswa wajib diisi',
            'attendances.array' => 'Data kehadiran harus berupa array',
            'attendances.min' => 'Minimal 1 data kehadiran',
            'attendances.*.student_id.required' => 'ID siswa wajib diisi',
            'attendances.*.student_id.exists' => 'Siswa tidak ditemukan',
            'attendances.*.status.required' => 'Status kehadiran wajib diisi',
            'attendances.*.status.in' => 'Status kehadiran tidak valid',
        ];
    }
}