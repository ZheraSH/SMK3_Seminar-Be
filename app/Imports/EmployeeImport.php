<?php

namespace App\Imports;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Models\Employee;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class EmployeeImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private array $groupedErrors = [];
    public int $importedCount = 0;

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $key = $failure->attribute() . '|' . $error;
                if (!isset($this->groupedErrors[$key])) {
                    $this->groupedErrors[$key] = [
                        'kolom'   => $failure->attribute(),
                        'message' => $error,
                        'rows'    => [],
                    ];
                }
                $this->groupedErrors[$key]['rows'][] = $failure->row();
            }
        }
    }

    public function getErrors(): array
    {
        return array_values($this->groupedErrors);
    }

    public function rules(): array
    {
        return [
            'nama'          => 'required|string|max:255',
            'nip'           => 'required|string|max:18',
            'agama'         => 'required|string',
            'jenis_kelamin' => 'required|in:L,P,l,p',
            'tempat_lahir'  => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat'        => 'required|string|max:500',
            // Nullable
            'nik'           => 'nullable|string|max:16',
            'nomor_hp'      => 'nullable|string|max:20',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'          => 'Kolom Nama wajib diisi.',
            'nama.max'               => 'Nama terlalu panjang, maksimal 255 karakter.',
            'nip.required'           => 'Kolom NIP wajib diisi.',
            'nip.max'                => 'NIP terlalu panjang, maksimal 18 karakter.',
            'jenis_kelamin.required' => 'Kolom Jenis Kelamin wajib diisi.',
            'jenis_kelamin.in'       => 'Jenis Kelamin hanya boleh diisi \'L\' (Laki-laki) atau \'P\' (Perempuan).',
            'tempat_lahir.required'  => 'Kolom Tempat Lahir wajib diisi.',
            'tempat_lahir.max'       => 'Tempat Lahir terlalu panjang, maksimal 255 karakter.',
            'tanggal_lahir.required' => 'Kolom Tanggal Lahir wajib diisi.',
            'tanggal_lahir.date'     => 'Format Tanggal Lahir tidak valid (contoh: 1990-08-17).',
            'alamat.required'        => 'Kolom Alamat wajib diisi.',
            'alamat.max'             => 'Alamat terlalu panjang, maksimal 500 karakter.',
            'nik.max'                => 'NIK terlalu panjang, maksimal 16 digit.',
            'nomor_hp.max'           => 'Nomor HP terlalu panjang, maksimal 20 karakter.',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nama'          => 'Nama',
            'nip'           => 'NIP',
            'agama'         => 'Agama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tempat_lahir'  => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'alamat'        => 'Alamat',
            'nik'           => 'NIK',
            'nomor_hp'      => 'Nomor HP',
        ];
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            DB::transaction(function () use ($row, $rowNumber) {
                if (Employee::where('nip', trim($row['nip']))->exists()) {
                    $key = 'nip_duplikat|' . $row['nip'];
                    if (!isset($this->groupedErrors[$key])) {
                        $this->groupedErrors[$key] = [
                            'kolom'   => 'nip',
                            'message' => "NIP {$row['nip']} sudah terdaftar di sistem.",
                            'rows'    => [],
                        ];
                    }
                    $this->groupedErrors[$key]['rows'][] = $rowNumber;
                    return;
                }

                $religionId = null;
                if (!empty($row['agama'])) {
                    $religion = Religion::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($row['agama'])) . '%'])->first();
                    if (!$religion) {
                        $key = 'agama_tidak_ditemukan|' . strtolower(trim($row['agama']));
                        if (!isset($this->groupedErrors[$key])) {
                            $this->groupedErrors[$key] = [
                                'kolom'   => 'agama',
                                'message' => "Agama '{$row['agama']}' tidak ditemukan di database.",
                                'rows'    => [],
                            ];
                        }
                        $this->groupedErrors[$key]['rows'][] = $rowNumber;
                        return;
                    }
                    $religionId = $religion->id;
                }

                $genderInput = strtoupper(trim($row['jenis_kelamin']));
                $gender = match ($genderInput) {
                    'L' => GenderEnum::MALE->value,
                    'P' => GenderEnum::FEMALE->value,
                    default => null,
                };

                if (!$gender) {
                    $key = 'gender_invalid|' . strtolower(trim($row['jenis_kelamin']));
                    if (!isset($this->groupedErrors[$key])) {
                        $this->groupedErrors[$key] = [
                            'kolom'   => 'jenis_kelamin',
                            'message' => "Jenis kelamin '{$row['jenis_kelamin']}' tidak valid (harus L atau P).",
                            'rows'    => [],
                        ];
                    }
                    $this->groupedErrors[$key]['rows'][] = $rowNumber;
                    return;
                }

                $email = trim($row['nip']) . '@skaniga.com';
                if (User::where('email', $email)->exists()) {
                    $email = trim($row['nip']) . '.' . Str::random(4) . '@skaniga.com';
                }

                $defaultImage = ($gender === GenderEnum::MALE->value)
                    ? 'default_image/teacher-boy.png'
                    : 'default_image/teacher-girl.png';
                $user = User::create([
                    'name'     => trim($row['nama']),
                    'slug'     => Str::slug(trim($row['nama'])) . '-' . Str::random(4),
                    'email'    => $email,
                    'password' => Hash::make(trim($row['nip'])),
                ]);

                $user->assignRole(RoleEnum::TEACHER->value);

                Employee::create([
                    'user_id'      => $user->id,
                    'nip'          => trim($row['nip']),
                    'nik'          => !empty($row['nik']) ? trim($row['nik']) : null,
                    'religion_id'  => $religionId,
                    'gender'       => $gender,
                    'birth_place'  => trim($row['tempat_lahir']),
                    'birth_date'   => \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
                    'address'      => trim($row['alamat']),
                    'phone_number' => !empty($row['nomor_hp']) ? trim($row['nomor_hp']) : null,
                    'image'        => $defaultImage,
                ]);

                $this->importedCount++;
            });
        }
    }
}
