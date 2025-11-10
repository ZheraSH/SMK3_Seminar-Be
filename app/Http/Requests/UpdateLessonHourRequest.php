<?php

namespace App\Http\Requests\LessonHour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id'); // ambil ID dari route

        return [
            'name' => 'required|string|max:100|unique:lesson_hours,name,' . $id,
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jam pelajaran wajib diisi',
            'name.unique' => 'Nama jam pelajaran sudah digunakan',
            'start.required' => 'Waktu mulai wajib diisi',
            'end.required' => 'Waktu selesai wajib diisi',
            'end.after' => 'Waktu selesai harus setelah waktu mulai',
        ];
    }
}
