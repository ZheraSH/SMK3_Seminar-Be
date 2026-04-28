<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

class LogStudentTapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendances'                   => 'required|array|min:1',
            'attendances.*.rfid'            => 'required|string',
            'attendances.*.checkin_time'    => 'nullable|date_format:Y-m-d H:i:s',
            'attendances.*.checkout_time'   => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'attendances.required'              => 'Data absensi tidak boleh kosong',
            'attendances.array'                 => 'Format data absensi tidak valid',
            'attendances.min'                   => 'Minimal 1 data absensi harus dikirim',
            'attendances.*.rfid.required'       => 'Nomor RFID wajib diisi',
            'attendances.*.checkin_time.date_format' => 'Format jam masuk harus Y-m-d H:i:s',
            'attendances.*.checkout_time.date_format' => 'Format jam pulang harus Y-m-d H:i:s',
        ];
    }
}
