<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;

class CheckMastercardRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfid' => 'required|string',
        ];
    }
}
