<?php

namespace App\Http\Requests\Homeroom_teacher;

use App\Http\Requests\ApiRequest;

class DailyAttendanceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'per_page.integer' => 'Per page harus berupa angka',
            'per_page.min' => 'Per page minimal 1',
            'per_page.max' => 'Per page maksimal 100',
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