<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class MarkLessonAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'teacher_journal_id' => 'required|exists:teacher_journals,id',
            'student_classroom_id' => 'required|exists:classroom_student,id',
            'lesson_hour_id' => 'required|exists:lesson_hours,id',
            'status' => 'required|in:present,late,sick,alpha,permit',
            'date' => 'nullable|date',
        ];
    }
}


