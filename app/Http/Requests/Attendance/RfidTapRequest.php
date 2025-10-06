<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class RfidTapRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rfid' => 'required|string',
            'timestamp' => 'nullable|date',
        ];
    }
}


