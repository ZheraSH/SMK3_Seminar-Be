<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class TapRfidRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfid' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.string' => 'Format RFID tidak valid',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->rfid) {
            $this->merge([
                'rfid' => trim($this->rfid),
            ]);
        }
    }
}
