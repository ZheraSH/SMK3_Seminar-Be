<?php

namespace App\Http\Requests\Operator;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('subjects', 'name')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mapel tidak boleh kosong.',
            'name.unique' => 'Nama mapel sudah terdaftar.',
            'name.max' => 'Nama mapel tidak boleh lebih dari 20 karakter.',
            'name.string' => 'Nama mapel harus berupa teks',
        ];
    }
}
