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
            'rfid' => 'required|min:7|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.min' => 'Nomor RFID minimal 7 karakter',
            'rfid.max' => 'Nomor RFID maksimal 15 karakter',
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
