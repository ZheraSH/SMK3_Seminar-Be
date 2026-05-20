<?php

namespace App\Http\Requests\Operator;

use Illuminate\Validation\Rule;
use App\Enums\SchoolTypeEnum;
use App\Enums\AccreditationEnum;
use App\Http\Requests\ApiRequest;
use App\Models\School;

class UpdateSchoolRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = School::first();
        $schoolId = $school ? $school->id : null;

        return [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'name' => 'sometimes|required|string|max:255',
            'principal_name' => 'sometimes|required|string|max:255',
            'npsn' => [
                'sometimes',
                'required',
                'numeric',
                'digits:8',
                Rule::unique(School::class, 'npsn')->ignore($schoolId),
            ],
            'phone' => 'sometimes|nullable|string|max:15',
            'email' => 'sometimes|nullable|email|max:100',
            'school_type' => [
                'sometimes',
                'nullable',
                Rule::in(SchoolTypeEnum::values()),
            ],
            'accreditation' => [
                'sometimes',
                'nullable',
                Rule::in(AccreditationEnum::values()),
            ],
            'address' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'Logo harus berupa file gambar',
            'logo.mimes' => 'Format logo harus jpeg, png, jpg, gif, atau svg',
            'logo.max' => 'Ukuran logo maksimal 4MB',
            'name.required' => 'Nama sekolah wajib diisi',
            'name.max' => 'Nama sekolah maksimal 255 karakter',
            'principal_name.required' => 'Nama kepala sekolah wajib diisi',
            'principal_name.max' => 'Nama kepala sekolah maksimal 255 karakter',
            'npsn.required' => 'NPSN wajib diisi',
            'npsn.numeric' => 'NPSN harus berupa angka',
            'npsn.digits' => 'NPSN harus terdiri dari 8 digit',
            'npsn.unique' => 'NPSN sudah terdaftar',
            'phone.max' => 'Nomor telepon maksimal 15 karakter',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 100 karakter',
            'school_type.in' => 'Jenis sekolah tidak valid',
            'accreditation.in' => 'Akreditasi sekolah tidak valid',
            'address.max' => 'Alamat maksimal 500 karakter',
        ];
    }

    public function attributes(): array
    {
        return [
            'logo' => 'logo sekolah',
            'name' => 'nama sekolah',
            'principal_name' => 'kepala sekolah',
            'npsn' => 'NPSN sekolah',
            'phone' => 'nomor telepon sekolah',
            'email' => 'email sekolah',
            'school_type' => 'jenis sekolah',
            'accreditation' => 'akreditasi sekolah',
            'address' => 'alamat sekolah',
        ];
    }
}