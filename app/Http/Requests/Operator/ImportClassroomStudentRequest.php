<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class ImportClassroomStudentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib di-upload.',
            'file.file' => 'Upload harus berupa file.',
            'file.mimes' => 'Format file harus berupa xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
