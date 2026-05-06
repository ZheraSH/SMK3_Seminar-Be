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
use Maatwebsite\Excel\Validators\Failure;

class ClassroomStudentImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{

    private array $groupedErrors = [];
    public int $importedCount = 0;
    private string $classroomId;

    public function __construct(string $classroomId)
    {
        $this->classroomId = $classroomId;
    }

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
            'nama' => 'required|string|max:255',
            'nisn' => 'required|numeric|digits:10',
            'agama' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P,l,p',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:500',
            // Nullable columns
            'nomor_kk' => 'nullable|numeric|digits:16',
            'nomor_akta' => 'nullable|numeric|digits_between:10,20',
            'anak_ke' => 'nullable|integer|min:1',
            'jumlah_saudara' => 'nullable|integer|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required' => 'Kolom Nama wajib diisi.',
            'nama.max' => 'Nama terlalu panjang, maksimal 255 karakter.',
            'nisn.required' => 'Kolom NISN wajib diisi.',
            'nisn.numeric' => 'NISN harus berupa angka.',
            'nisn.digits' => 'NISN harus tepat 10 digit angka.',
            'agama.required' => 'Kolom Agama wajib diisi.',
            'jenis_kelamin.required' => 'Kolom Jenis Kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis Kelamin hanya boleh diisi \'L\' (Laki-laki) atau \'P\' (Perempuan).',
            'tempat_lahir.required' => 'Kolom Tempat Lahir wajib diisi.',
            'tempat_lahir.max' => 'Tempat Lahir terlalu panjang, maksimal 255 karakter.',
            'tanggal_lahir.required' => 'Kolom Tanggal Lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format Tanggal Lahir tidak valid (contoh: 2005-08-17).',
            'alamat.required' => 'Kolom Alamat wajib diisi.',
            'alamat.max' => 'Alamat terlalu panjang, maksimal 500 karakter.',
            'nomor_kk.numeric' => 'Nomor KK harus berupa angka.',
            'nomor_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
            'nomor_akta.numeric' => 'Nomor Akta harus berupa angka.',
            'nomor_akta.digits_between' => 'Nomor Akta harus antara 10 sampai 20 digit angka.',
            'anak_ke.integer' => 'Kolom Anak Ke harus berupa angka bulat.',
            'anak_ke.min' => 'Anak Ke minimal bernilai 1.',
            'jumlah_saudara.integer' => 'Jumlah Saudara harus berupa angka bulat.',
            'jumlah_saudara.min' => 'Jumlah Saudara tidak boleh bernilai negatif.',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nama' => 'Nama',
            'nisn' => 'NISN',
            'agama' => 'Agama',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'alamat' => 'Alamat',
            'nomor_kk' => 'Nomor KK',
            'nomor_akta' => 'Nomor Akta',
            'anak_ke' => 'Anak Ke',
            'jumlah_saudara' => 'Jumlah Saudara',
        ];
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            DB::transaction(function () use ($row, $rowNumber) {
                if (Student::where('nisn', $row['nisn'])->exists()) {
                    $key = 'nisn_duplikat|' . $row['nisn'];
                    $this->groupedErrors[$key] = [
                        'kolom' => 'nisn',
                        'message' => "NISN {$row['nisn']} sudah terdaftar di sistem.",
                        'rows' => [$rowNumber],
                    ];
                    return;
                }

                $religion = Religion::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($row['agama'])) . '%'])->first();
                if (!$religion) {
                    $key = 'agama_tidak_ditemukan|' . strtolower(trim($row['agama']));
                    if (!isset($this->groupedErrors[$key])) {
                        $this->groupedErrors[$key] = [
                            'kolom' => 'agama',
                            'message' => "Agama '{$row['agama']}' tidak ditemukan di database.",
                            'rows' => [],
                        ];
                    }
                    $this->groupedErrors[$key]['rows'][] = $rowNumber;
                    return;
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
                            'kolom' => 'jenis_kelamin',
                            'message' => "Jenis kelamin '{$row['jenis_kelamin']}' tidak valid (harus L atau P).",
                            'rows' => [],
                        ];
                    }
                    $this->groupedErrors[$key]['rows'][] = $rowNumber;
                    return;
                }

                $email = trim($row['nisn']) . '@skaniga.com';

                if (User::where('email', $email)->exists()) {
                    $email = trim($row['nisn']) . '.' . Str::random(4) . '@skaniga.com';
                }

                $user = User::create([
                    'name' => trim($row['nama']),
                    'slug' => Str::slug(trim($row['nama'])) . '-' . Str::random(4),
                    'email' => $email,
                    'password' => Hash::make($row['nisn']),
                ]);
                $user->assignRole(RoleEnum::STUDENT->value);

                $student = Student::create([
                    'user_id' => $user->id,
                    'nisn' => trim($row['nisn']),
                    'religion_id' => $religion->id,
                    'gender' => $gender,
                    'image' => ($gender === GenderEnum::MALE->value) ? 'default_image/student-boy.png' : 'default_image/student-girl.png',
                    'birth_place' => trim($row['tempat_lahir']),
                    'birth_date' => \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
                    'address' => trim($row['alamat']),
                    'number_kk' => trim($row['nomor_kk']),
                    'number_akta' => trim($row['nomor_akta']),
                    'order_child' => !empty($row['anak_ke']) ? (int) $row['anak_ke'] : null,
                    'count_siblings' => !empty($row['jumlah_saudara']) ? (int) $row['jumlah_saudara'] : null,
                    'status' => StudentStatusEnum::ACTIVE->value,
                    'point' => 0,
                ]);

                ClassroomStudents::create([
                    'classroom_id' => $this->classroomId,
                    'student_id' => $student->id,
                    'status' => StudentStatusEnum::ACTIVE->value,
                    'active_unique_guard' => $student->id,
                ]);

                $this->importedCount++;
            });
        }
    }
}
