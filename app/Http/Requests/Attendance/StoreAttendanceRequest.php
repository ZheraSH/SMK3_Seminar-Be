<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'classroom_student_id' => 'required|exists:classroom_students,id',
            'status' => 'required|in:present,late,sick,alpha,permit',
            'point' => 'nullable|integer|min:0',
            'proof' => 'nullable|string|max:255',
        ];
    }
}


