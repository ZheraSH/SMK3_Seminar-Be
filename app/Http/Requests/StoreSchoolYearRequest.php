<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSchoolYearRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|unique:school_years,name',
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
