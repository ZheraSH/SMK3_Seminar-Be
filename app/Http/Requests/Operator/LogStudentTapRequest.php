<?php

namespace App\Http\Requests\Operator;

use App\Enums\RoleEnum;
use App\Http\Requests\ApiRequest;

class LogStudentTapRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'attendances'           => 'required|array|min:1',
            'attendances.*.rfid'    => 'required|string',
            'attendances.*.type'    => 'required|in:' . RoleEnum::STUDENT->value,
            'attendances.*.checkin_time'  => 'nullable|date_format:Y-m-d H:i:s',
            'attendances.*.checkout_time' => 'nullable|date_format:Y-m-d H:i:s',
            'date'                  => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'attendances.required'                    => 'Data absensi tidak boleh kosong',
            'attendances.array'                       => 'Format data absensi tidak valid',
            'attendances.min'                         => 'Minimal 1 data absensi harus dikirim',
            'attendances.*.rfid.required'             => 'Nomor RFID wajib diisi',
            'attendances.*.type.required'             => 'Tipe pengguna wajib diisi',
            'attendances.*.type.in'                   => 'Tipe pengguna tidak valid, harus: ' . RoleEnum::STUDENT->value,
            'attendances.*.checkin_time.date_format'  => 'Format jam masuk harus Y-m-d H:i:s',
            'attendances.*.checkout_time.date_format' => 'Format jam pulang harus Y-m-d H:i:s',
            'date.required'                           => 'Tanggal absensi wajib diisi',
            'date.date'                               => 'Format tanggal tidak valid',
        ];
    }
}