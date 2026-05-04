<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class UpdateLessonHourRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
        ];
    }

    public function messages(): array
    {
        return [
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