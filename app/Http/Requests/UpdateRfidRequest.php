<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Enums\RfidStatusEnum;

class UpdateRfidRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rfidId = $this->route('rfid')?->id;

        return [
            'rfid' => [
                'sometimes|string|max:255',
                Rule::unique('rfids')->ignore($rfidId)
            ],
            'student_id' => 'sometimes|exists:students,id',
            'status' => 'sometimes|in:' . implode(',', RfidStatusEnum::values()),
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.unique' => 'Nomor RFID sudah terdaftar',
            'student_id.exists' => 'Siswa yang dipilih tidak valid',
            'status.in' => 'Status harus active atau inactive',
        ];
    }
}