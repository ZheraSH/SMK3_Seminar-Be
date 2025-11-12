<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRuleByDayRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkin_start' => 'required_if:is_holiday,false|nullable|date_format:H:i',
            'checkin_end' => 'required_if:is_holiday,false|nullable|date_format:H:i|after:checkin_start',
            'checkout_start' => 'required_if:is_holiday,false|nullable|date_format:H:i|after:checkin_end',
            'checkout_end' => 'required_if:is_holiday,false|nullable|date_format:H:i|after:checkout_start',
            'is_holiday' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'checkin_start.required_if' => 'Waktu mulai check-in wajib diisi ketika bukan hari libur',
            'checkin_end.required_if' => 'Waktu akhir check-in wajib diisi ketika bukan hari libur',
            'checkin_end.after' => 'Waktu akhir check-in harus setelah waktu mulai check-in',
            'checkout_start.required_if' => 'Waktu mulai check-out wajib diisi ketika bukan hari libur',
            'checkout_start.after' => 'Waktu mulai check-out harus setelah waktu akhir check-in',
            'checkout_end.required_if' => 'Waktu akhir check-out wajib diisi ketika bukan hari libur',
            'checkout_end.after' => 'Waktu akhir check-out harus setelah waktu mulai check-out',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_holiday' => $this->boolean('is_holiday'),
        ]);
    }
}