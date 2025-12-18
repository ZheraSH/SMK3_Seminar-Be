<?php

namespace App\Http\Requests\Operator;

use App\Enums\RfidStatusEnum;
use App\Http\Requests\ApiRequest;

class StoreRfidRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfid' => ['required', 'digits:10', 'unique:rfids,rfid'],
            'student_id' => 'required|exists:students,id',
            'status' => 'sometimes|in:' . implode(',', RfidStatusEnum::values()),
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.unique' => 'Nomor RFID sudah terdaftar',
            'rfid.max' => 'Nomor RFID Maximal angka 10',
            'student_id.required' => 'Siswa wajib dipilih',
            'student_id.exists' => 'Siswa yang dipilih tidak valid',
            'status.in' => 'Status harus active atau inactive',
        ];
    }

    protected function prepareForValidation()
    {
        if (!$this->has('status')) {
            $this->merge([
                'status' => RfidStatusEnum::ACTIVE->value,
            ]);
        }
    }
}