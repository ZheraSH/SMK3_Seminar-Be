<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Enums\DayEnum;

class StoreLessonHourRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day' => 'required|in:' . implode(',', DayEnum::values()),
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('lesson_hours', 'name')->where('day', $this->day)->whereNull('deleted_at')
            ],
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
        ];
    }

    public function messages(): array
    {
        return [
            'day.required' => 'Hari wajib dipilih',
            'day.in' => 'Hari tidak valid',
            'name.required' => 'Nama jam pelajaran wajib diisi',
            'name.unique' => 'Nama jam pelajaran sudah digunakan untuk hari ini',
            'start.required' => 'Waktu mulai wajib diisi',
            'start.date_format' => 'Format waktu mulai harus HH:MM',
            'end.required' => 'Waktu selesai wajib diisi',
            'end.date_format' => 'Format waktu selesai harus HH:MM',
            'end.after' => 'Waktu selesai harus setelah waktu mulai',
        ];
    }

    public function attributes(): array
    {
        return [
            'day' => 'hari',
            'name' => 'nama jam pelajaran',
            'start' => 'waktu mulai',
            'end' => 'waktu selesai',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->start) {
            $this->merge([
                'start' => $this->formatTime($this->start),
            ]);
        }

        if ($this->end) {
            $this->merge([
                'end' => $this->formatTime($this->end),
            ]);
        }
    }

    private function formatTime($time): string
    {
        if (strpos($time, '.') !== false) {
            return str_replace('.', ':', $time);
        }
        
        return $time;
    }
}