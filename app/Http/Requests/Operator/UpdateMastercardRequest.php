<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;
use App\Rules\ValidRfidNumber;
use Illuminate\Validation\Rule;

class UpdateMastercardRequest extends ApiRequest
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
                'digits:10',
                Rule::unique('mastercards', 'rfid')->ignore($this->route('mastercard')),
                new ValidRfidNumber(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rfid.required' => 'Nomor RFID wajib diisi',
            'rfid.digits' => 'Nomor RFID harus berupa 10 digit angka',
            'rfid.unique' => 'Nomor RFID sudah terdaftar',
        ];
    }
}
