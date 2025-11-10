<?php
namespace App\Http\Requests;

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
            'name' => 'required|string|max:100',
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jam pelajaran wajib diisi',
            'start.required' => 'Waktu mulai wajib diisi',
            'start.date_format' => 'Format waktu mulai harus HH:MM',
            'end.required' => 'Waktu selesai wajib diisi',
            'end.date_format' => 'Format waktu selesai harus HH:MM',
            'end.after' => 'Waktu selesai harus setelah waktu mulai',
        ];
    }

    /**
     * Prepare the data for validation.
     */
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