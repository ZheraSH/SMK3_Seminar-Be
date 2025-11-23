<?php

namespace App\Http\Requests;

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
            'subject_id' => 'required|exists:subjects,id',
            'lesson_schedule_id' => 'required|exists:lesson_schedules,id',
            'date' => 'required|date',
            'lesson_order' => 'required|integer|min:2',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:hadir,terlambat,alpha,izin,sakit',
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Kelas wajib dipilih',
            'subject_id.required' => 'Mata pelajaran wajib dipilih',
            'lesson_order.required' => 'Urutan jam pelajaran wajib diisi',
            'lesson_order.min' => 'Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya',
            'attendances.required' => 'Data kehadiran siswa wajib diisi',
            'attendances.*.student_id.required' => 'Siswa wajib dipilih',
            'attendances.*.status.required' => 'Status kehadiran wajib diisi',
        ];
    }
}