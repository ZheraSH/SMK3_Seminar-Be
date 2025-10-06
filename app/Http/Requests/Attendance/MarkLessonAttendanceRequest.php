<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\ApiRequest;

class MarkLessonAttendanceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_journal_id' => 'required|exists:teacher_journals,id',
            'student_classroom_id' => 'required|exists:classroom_student,id',
            'lesson_hour_id' => 'required|exists:lesson_hours,id',
            'status' => 'required|in:present,late,sick,alpha,permit',
            'date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_journal_id.required' => 'Jurnal guru tidak boleh kosong',
            'teacher_journal_id.exists' => 'Jurnal guru tidak valid',
            'student_classroom_id.required' => 'Siswa tidak boleh kosong',
            'student_classroom_id.exists' => 'Siswa tidak valid',
            'lesson_hour_id.required' => 'Jam pelajaran tidak boleh kosong',
            'lesson_hour_id.exists' => 'Jam pelajaran tidak valid',
            'status.required' => 'Status tidak boleh kosong',
            'status.in' => 'Status tidak valid',
            'date.date' => 'Tanggal tidak valid',
        ];
    }
}


