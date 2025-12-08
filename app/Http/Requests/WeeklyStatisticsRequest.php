<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class WeeklyStatisticsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.date' => 'Format tanggal mulai tidak valid',
            'end_date.required' => 'Tanggal selesai wajib diisi',
            'end_date.date' => 'Format tanggal selesai tidak valid',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai',
        ];
    }

    protected function prepareForValidation()
    {
        foreach (['start_date', 'end_date'] as $field) {
            if ($this->has($field)) {
                try {
                    $date = \Carbon\Carbon::parse($this->$field)->format('Y-m-d');
                    $this->merge([$field => $date]);
                } catch (\Exception $e) {
                }
            }
        }
    }
}