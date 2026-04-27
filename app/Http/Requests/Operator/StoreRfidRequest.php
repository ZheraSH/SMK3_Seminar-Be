<?php

namespace App\Http\Requests\Operator;

use App\Enums\RfidStatusEnum;
use App\Http\Requests\ApiRequest;
use App\Rules\ValidRfidNumber;

class StoreRfidRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfid' => [
                'required',
                'min:7',
                'max:15',
                'unique:rfids,rfid',
                new ValidRfidNumber(),
            ],
            'student_id' => 'required|exists:students,id',
            'status' => 'sometimes|in:' . implode(',', RfidStatusEnum::values()),
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.unique' => 'Nomor RFID sudah terdaftar',
            'rfid.min' => 'Nomor RFID minimal 7 karakter',
            'rfid.max' => 'Nomor RFID maksimal 15 karakter',
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
