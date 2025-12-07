<?php

namespace App\Http\Requests;


class UpdateSubjectRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
   public function rules(): array
{
    $id = $this->route('id');
    return [
        'name' => 'required|string|max:255|unique:subjects,name,' . $id . ',id,deleted_at,NULL',
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