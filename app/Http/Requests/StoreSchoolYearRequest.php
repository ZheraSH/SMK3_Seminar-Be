<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSchoolYearRequest extends ApiRequest
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
        return [
            'name' => 'required|string|unique:school_years,name',
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
