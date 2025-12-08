<?php

namespace App\Http\Requests;

class UpdateSchoolYearRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('school_year');

        return [
            'name' => 'required|string|unique:school_years,name,' . $id,
            'active' => 'boolean',
        ];
    }

        public function messages(): array
    {
        return [
            'name.required' => 'Tahun ajaran tidak boleh kosong',
            'name.unique' => 'Tahun ajaran sudah digunakan',
            'name.string' => 'Tahun ajaran harus berupa teks',
            'active.boolean' => 'Status aktif harus berupa true atau false',
        ];
    }
}