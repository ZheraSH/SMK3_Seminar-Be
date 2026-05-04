<?php

namespace App\Http\Requests\Operator;

use App\Enums\DayEnum;
use App\Http\Requests\ApiRequest;

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
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'is_lesson' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'day.required' => 'Hari wajib dipilih',
            'day.in' => 'Hari tidak valid',
            'start.required' => 'Waktu mulai wajib diisi',
            'start.date_format' => 'Format waktu mulai harus HH:MM',
            'end.required' => 'Waktu selesai wajib diisi',
            'end.date_format' => 'Format waktu selesai harus HH:MM',
            'end.after' => 'Waktu selesai harus setelah waktu mulai',
            'is_lesson.required' => 'Tipe jam wajib dipilih',
            'is_lesson.boolean' => 'Tipe jam tidak valid',
        ];
    }

    public function attributes(): array
    {
        return [
            'day' => 'hari',
            'start' => 'waktu mulai',
            'end' => 'waktu selesai',
            'is_lesson' => 'tipe jam',
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

        if ($this->has('is_lesson')) {
            $isLesson = $this->is_lesson;
            if (is_string($isLesson)) {
                $this->merge([
                    'is_lesson' => filter_var($isLesson, FILTER_VALIDATE_BOOLEAN),
                ]);
            }
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