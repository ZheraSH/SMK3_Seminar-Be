<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Employee;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;

class StoreEmployeeRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeRoles = RoleEnum::values();

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email'),
            ],
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'NIP' => [
                'required',
                'string',
                'max:18',
                Rule::unique(Employee::class, 'NIP'),
            ],
            'NIK' => 'nullable|string|max:16',
            'religion_id' => 'nullable|exists:religions,id',
            'gender' => 'required|in:' . implode(',', GenderEnum::values()),
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'roles' => 'required|array|min:1',
            'roles.*' => Rule::in($employeeRoles),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'image.mimes' => 'Foto harus berekstensi png, jpg, atau jpeg',
            'image.max' => 'Ukuran foto maksimal 2MB',
            'NIP.required' => 'NIP tidak boleh kosong',
            'NIP.unique' => 'NIP sudah digunakan',
            'NIP.max' => 'NIP maksimal 18 karakter',
            'NIK.max' => 'NIK maksimal 16 karakter',
            'religion_id.exists' => 'Agama tidak ditemukan',
            'gender.required' => 'Jenis kelamin tidak boleh kosong',
            'gender.in' => 'Jenis kelamin tidak valid',
            'birth_date.required' => 'Tanggal lahir tidak boleh kosong',
            'birth_date.date' => 'Tanggal lahir harus valid',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini',
            'birth_place.required' => 'Tempat lahir tidak boleh kosong',
            'birth_place.max' => 'Tempat lahir maksimal 255 karakter',
            'address.required' => 'Alamat tidak boleh kosong',
            'address.max' => 'Alamat maksimal 500 karakter',
            'phone_number.max' => 'Nomor telepon maksimal 20 karakter',
            'roles.required' => 'Role tidak boleh kosong',
            'roles.array' => 'Role harus berupa array',
            'roles.min' => 'Pilih minimal 1 role',
            'roles.*.in' => 'Role yang dipilih tidak valid',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'image' => 'foto',
            'NIP' => 'NIP',
            'NIK' => 'NIK',
            'religion_id' => 'agama',
            'gender' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir',
            'birth_place' => 'tempat lahir',
            'address' => 'alamat',
            'phone_number' => 'nomor telepon',
            'roles' => 'peran',
        ];
    }
}