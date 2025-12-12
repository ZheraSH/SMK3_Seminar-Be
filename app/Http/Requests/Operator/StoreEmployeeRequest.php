<?php

namespace App\Http\Requests\Operator;

use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Employee;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Http\Requests\ApiRequest;

class StoreEmployeeRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email'),
            ],
            'image' => 'nullable|mimes:png,jpg,jpeg|max:1024',
            'nip'   => [
                'required',
                'string',
                'max:18',
                Rule::unique(Employee::class, 'nip'),
            ],
            'nik' => 'nullable|string|max:16',
            'religion_id' => 'nullable|exists:religions,id',
            'gender' => 'required|in:' . implode(',', GenderEnum::values()),
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'roles' => ['required', 'array', 'min:1', 'max:2'],
            'roles.*' => ['required', 'string', Rule::in(RoleEnum::values())],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roles = $this->input('roles', []);

            if (empty($roles)) {
                return;
            }

            foreach ($roles as $role) {
                if (!in_array($role, RoleEnum::values())) {
                    $validator->errors()->add('roles', "Role '{$role}' tidak valid.");
                    return;
                }
            }

            $teacherRoles = RoleEnum::teacherRoles();
            $staffRoles = RoleEnum::staffRoles();

            $teacherCount = count(array_intersect($roles, $teacherRoles));
            $staffCount = count(array_intersect($roles, $staffRoles));

            if ($teacherCount > 0 && $staffCount > 0) {
                $validator->errors()->add('roles', 'Tidak boleh memilih role dari kategori guru dan staff sekaligus.');
                return;
            }

            if ($teacherCount > 0) {
                if (count($roles) > 2) {
                    $validator->errors()->add('roles', 'Kategori guru maksimal memilih 2 role.');
                }

                if (!$this->isValidTeacherCombination($roles)) {
                    $validator->errors()->add('roles', 'Kombinasi role guru tidak valid. Pilihan valid: Guru Pengajar + Wali Kelas, Guru Pengajar + BK, Wali Kelas + BK, atau salah satu saja.');
                }
            }

            if ($staffCount > 0) {
                if (count($roles) > 1) {
                    $validator->errors()->add('roles', 'Kategori staff hanya boleh memilih 1 role.');
                }

                if (!$this->isValidStaffRole($roles)) {
                    $validator->errors()->add('roles', 'Role staff tidak valid. Pilihan: Staff TU atau Waka Kurikulum.');
                }
            }
        });
    }

    private function isValidTeacherCombination(array $roles): bool
    {
        $teacherRoles = RoleEnum::teacherRoles();

        $filteredRoles = array_intersect($roles, $teacherRoles);

        if (count($filteredRoles) === 1) {
            return true;
        }

        if (count($filteredRoles) === 2) {
            $sortedRoles = sort($filteredRoles);
            $validCombinations = [
                [RoleEnum::TEACHER->value, RoleEnum::HOMEROOM_TEACHER->value],
                [RoleEnum::TEACHER->value, RoleEnum::COUNSELOR->value],
                [RoleEnum::HOMEROOM_TEACHER->value, RoleEnum::COUNSELOR->value],
            ];
            
            return in_array($sortedRoles, $validCombinations);
        }
        
        return false;
    }

    private function isValidStaffRole(array $roles): bool
    {
        $staffRoles = RoleEnum::staffRoles();

        $filteredRoles = array_intersect($roles, $staffRoles);

        if (count($filteredRoles) !== 1) {
            return false;
        }

        $role = reset($filteredRoles);
        return in_array($role, $staffRoles);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'image.mimes' => 'Foto harus berekstensi png, jpg, atau jpeg',
            'image.max' => 'Ukuran foto maksimal 1MB',
            'nip.required' => 'nip tidak boleh kosong',
            'nip.unique' => 'nip sudah digunakan',
            'nip.max' => 'nip maksimal 18 karakter',
            'nik.max' => 'nik maksimal 16 karakter',
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
            'roles.max' => 'Maksimal 2 role',
            'roles.*.required' => 'Role tidak boleh kosong',
            'roles.*.in' => 'Role yang dipilih tidak valid',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'image' => 'foto',
            'nip' => 'nip',
            'nik' => 'nik',
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