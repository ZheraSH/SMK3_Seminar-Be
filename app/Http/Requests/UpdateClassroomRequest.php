<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\Classroom;

class UpdateClassroomRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        
        return [
            'teacher_id' => 'required','exists:employees,id',
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Guru/Wali kelas harus dipilih',
            'teacher_id.exists' => 'Guru/Wali kelas tidak ditemukan',
        ];
    }
}