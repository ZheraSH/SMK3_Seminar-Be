<?php

namespace App\Imports;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Enums\StudentStatusEnum;
use App\Models\ClassroomStudents;
use App\Models\Religion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ClassroomStudentImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public array $errors = [];
    public int $importedCount = 0;
    private string $classroomId;

    public function __construct(string $classroomId)
    {
        $this->classroomId = $classroomId;
    }

    public function rules(): array
    {
        return [
            'nama'         => 'required|string|max:255',
            'nisn'         => 'required|numeric|digits:10',
            'agama'        => 'required|string',
            'jenis_kelamin'=> 'required|in:L,P,l,p',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir'=> 'required|date',
            'alamat'       => 'required|string|max:500',
            'nomor_kk'     => 'required|numeric|digits:16',
            'nomor_akta'   => 'required|numeric|digits_between:10,20',
            // Nullable columns
            'anak_ke'         => 'nullable|integer|min:1',
            'jumlah_saudara'  => 'nullable|integer|min:0',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nama'          => 'Nama',
            'nisn'          => 'NISN',
            'agama'         => 'Agama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tempat_lahir'  => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'alamat'        => 'Alamat',
            'nomor_kk'      => 'Nomor KK',
            'nomor_akta'    => 'Nomor Akta',
            'anak_ke'       => 'Anak Ke',
            'jumlah_saudara'=> 'Jumlah Saudara',
        ];
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            DB::transaction(function () use ($row, $rowNumber) {
                if (Student::where('nisn', $row['nisn'])->exists()) {
                    $this->errors[] = [
                        'row'     => $rowNumber,
                        'nisn'    => $row['nisn'],
                        'message' => "NISN {$row['nisn']} sudah terdaftar di sistem, baris dilewati.",
                    ];
                    return;
                }

                $religion = Religion::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($row['agama'])) . '%'])->first();
                if (!$religion) {
                    $this->errors[] = [
                        'row'     => $rowNumber,
                        'nisn'    => $row['nisn'],
                        'message' => "Agama '{$row['agama']}' tidak ditemukan di database, baris dilewati.",
                    ];
                    return;
                }

                $genderInput = strtoupper(trim($row['jenis_kelamin']));
                $gender = match ($genderInput) {
                    'L' => GenderEnum::MALE->value,
                    'P' => GenderEnum::FEMALE->value,
                    default => null,
                };

                if (!$gender) {
                    $this->errors[] = [
                        'row'     => $rowNumber,
                        'nisn'    => $row['nisn'],
                        'message' => "Jenis kelamin '{$row['jenis_kelamin']}' tidak valid (harus L atau P), baris dilewati.",
                    ];
                    return;
                }

                $email = trim($row['nisn']) . '@skaniga.com';

                if (User::where('email', $email)->exists()) {
                    $email = trim($row['nisn']) . '.' . Str::random(4) . '@skaniga.com';
                }

                $user = User::create([
                    'name'     => trim($row['nama']),
                    'slug'     => Str::slug(trim($row['nama'])) . '-' . Str::random(4),
                    'email'    => $email,
                    'password' => Hash::make($row['nisn']),
                ]);
                $user->assignRole(RoleEnum::STUDENT->value);

                $student = Student::create([
                    'user_id'        => $user->id,
                    'nisn'           => trim($row['nisn']),
                    'religion_id'    => $religion->id,
                    'gender'         => $gender,
                    'birth_place'    => trim($row['tempat_lahir']),
                    'birth_date'     => \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
                    'address'        => trim($row['alamat']),
                    'number_kk'      => trim($row['nomor_kk']),
                    'number_akta'    => trim($row['nomor_akta']),
                    'order_child'    => !empty($row['anak_ke']) ? (int) $row['anak_ke'] : null,
                    'count_siblings' => !empty($row['jumlah_saudara']) ? (int) $row['jumlah_saudara'] : null,
                    'status'         => StudentStatusEnum::ACTIVE->value,
                    'point'          => 0,
                ]);

                ClassroomStudents::create([
                    'classroom_id' => $this->classroomId,
                    'student_id'   => $student->id,
                    'status'       => StudentStatusEnum::ACTIVE->value,
                ]);

                $this->importedCount++;
            });
        }
    }
}
