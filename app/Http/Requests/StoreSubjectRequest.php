<?php

namespace App\Http\Requests;

class StoreSubjectRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:20|unique:subjects,name,NULL,id,deleted_at,NULL',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mapel tidak boleh kosong.',
            'name.unique' => 'Nama mapel sudah terdaftar.',
            'name.string' => 'Nama mapel harus berupa teks',
        ];
    }
}
