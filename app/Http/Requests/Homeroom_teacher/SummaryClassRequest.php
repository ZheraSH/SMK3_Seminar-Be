<?php

namespace App\Http\Requests\Homeroom_teacher;

use App\Http\Requests\ApiRequest;

class SummaryClassRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('date')) {
            try {
                $date = \Carbon\Carbon::parse($this->date)->format('Y-m-d');
                $this->merge(['date' => $date]);
            } catch (\Exception $e) {
            }
        }
    }
}