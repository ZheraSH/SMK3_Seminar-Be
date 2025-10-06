<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'sometimes|required|in:present,late,sick,alpha,permit',
            'point' => 'sometimes|nullable|integer|min:0',
            'proof' => 'sometimes|nullable|string|max:255',
        ];
    }
}


