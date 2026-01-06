<?php

namespace App\Http\Requests;


class UpdateSubjectRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'name' => 'required|string|max:20|unique:subjects,name,' . $id . ',id,deleted_at,NULL',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mapel tidak boleh kosong.',
            'name.unique' => 'Nama mapel sudah digunakan.',
            'name.string' => 'Nama mapel harus berupa teks',
        ];
    }
}