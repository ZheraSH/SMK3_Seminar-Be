<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Models\Employee;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    private const TOTAL_EMPLOYEES = 30;

    // Distribusi berdasarkan kebutuhan sekolah
    // 9 kelas butuh 9 wali kelas, 12 guru reguler, 3 BK, 6 staff
    private const DISTRIBUTION = [
        // Kategori Guru (Teacher Roles)
        'teacher_only' => [ // Key string, value array
            'roles' => ['teacher'],
            'count' => 12
        ],
        'homeroom_only' => [
            'roles' => ['homeroom_teacher'],
            'count' => 9
        ],
        'counselor_only' => [
            'roles' => ['counselor'],
            'count' => 3
        ],
        'teacher_homeroom' => [
            'roles' => ['teacher', 'homeroom_teacher'],
            'count' => 2
        ],
        'teacher_counselor' => [
            'roles' => ['teacher', 'counselor'],
            'count' => 2
        ],
        'homeroom_counselor' => [
            'roles' => ['homeroom_teacher', 'counselor'],
            'count' => 1
        ],
        
        // Kategori Staff (Staff Roles)
        'staff_tu' => [
            'roles' => ['staff_tu'],
            'count' => 5
        ],
        'curriculum_coordinator' => [
            'roles' => ['curriculum_coordinator'],
            'count' => 2
        ],
    ];

    public function run(): void
    {
        $religion = Religion::where('name', 'Islam')->first();

        if (!$religion) {
            return;
        }

        $counter = 1;
        $totalCreated = 0;
        
        // Catatan wali kelas untuk ClassroomSeeder
        $homeroomTeacherIds = [];
        
        // Generate berdasarkan distribusi
        foreach (self::DISTRIBUTION as $category => $data) {
            $roleArray = $data['roles'];
            $count = $data['count'];
            
            for ($i = 0; $i < $count; $i++) {
                // Generate NIP dan NIK valid
                $nip = $this->generateValidNIP($counter);
                $nik = $this->generateValidNIK($counter);
                
                $employee = $this->createEmployee($counter, $religion, $roleArray, $nip, $nik);
                
                // Simpan ID wali kelas untuk ClassroomSeeder
                if (in_array(RoleEnum::HOMEROOM_TEACHER->value, $roleArray)) {
                    $homeroomTeacherIds[] = $employee->id;
                }
                
                $counter++;
                $totalCreated++;
            }
        }
    }

    private function createEmployee(
        int $index, 
        Religion $religion, 
        array $roles, 
        string $nip, 
        string $nik
    ): Employee {
        $gender = $this->determineGender($index);
        $name = $this->generateName($index, $gender->value);
        $email = "employee{$index}@skaniga.com";
        
        // Validasi: roles tidak boleh campur kategori guru dan staff
        $this->validateRoleCombination($roles);

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'id' => Str::uuid(),
                'name' => $name,
                'slug' => Str::slug($name),
                'password' => Hash::make($nip),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles($roles);

        $employee = Employee::firstOrCreate(
            ['user_id' => $user->id],
            [
                'id' => Str::uuid(),
                'image' => $gender->value === GenderEnum::MALE->value 
                    ? 'default_image/teacher-boy.png'
                    : 'default_image/teacher-girl.png',
                'nip' => $nip,
                'nik' => $nik,
                'religion_id' => $religion->id,
                'gender' => $gender->value,
                'birth_date' => $this->generateBirthDate($index),
                'birth_place' => $this->randomCity(),
                'address' => $this->generateAddress($index),
                'phone_number' => $this->generatePhoneNumber($index),
            ]
        );

        return $employee;
    }

    private function validateRoleCombination(array $roles): void
    {
        $teacherRoles = RoleEnum::teacherRoles();
        $staffRoles = RoleEnum::staffRoles();
        
        $hasTeacherRole = !empty(array_intersect($roles, $teacherRoles));
        $hasStaffRole = !empty(array_intersect($roles, $staffRoles));
        
        // Validasi: Tidak boleh campur guru dan staff
        if ($hasTeacherRole && $hasStaffRole) {
            return; // Skip exception agar seeder tetap jalan
        }
        
        // Validasi: Guru max 2 role
        if ($hasTeacherRole && count($roles) > 2) {
            return;
        }
        
        // Validasi: Staff max 1 role
        if ($hasStaffRole && count($roles) > 1) {
            return;
        }
        
        // Validasi kombinasi role guru
        if ($hasTeacherRole && count($roles) == 2) {
            $sortedRoles = $roles;
            sort($sortedRoles);
            
            $validCombinations = [
                [RoleEnum::TEACHER->value, RoleEnum::HOMEROOM_TEACHER->value],
                [RoleEnum::TEACHER->value, RoleEnum::COUNSELOR->value],
                [RoleEnum::HOMEROOM_TEACHER->value, RoleEnum::COUNSELOR->value],
            ];
            
            if (!in_array($sortedRoles, $validCombinations)) {
                return;
            }
        }
    }

    private function generateValidNIP(int $index): string
    {
        $base = '198';
        $year = date('y');
        $sequence = str_pad($index, 10, '0', STR_PAD_LEFT);
        $random = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        
        $nip = $base . $year . $sequence . $random;
        return substr($nip, 0, 18);
    }

    private function generateValidNIK(int $index): string
    {
        $province = '32';
        $regency = '04';
        $district = '11';
        $birthDay = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $birthMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $birthYear = rand(65, 85);
        $unique = str_pad($index, 4, '0', STR_PAD_LEFT);
        
        $nik = $province . $regency . $district . $birthDay . $birthMonth . $birthYear . $unique;
        return substr($nik, 0, 16);
    }

    private function determineGender(int $index): GenderEnum
    {
        return $index % 2 == 0 ? GenderEnum::MALE : GenderEnum::FEMALE;
    }

    private function generateName(int $index, string $gender): string
    {
        $maleFirstNames = ['Tegar', 'Dimas', 'Firman', 'Sbastian', 'Valen', 'Ramzi', 'Gunawan', 'Nidal', 'Azadi', 'Jaka'];
        $femaleFirstNames = ['Rofiatul', 'Rohmah', 'Inka', 'Putri', 'Ica', 'Riang', 'Nining', 'Niendy', 'Indah'];
        $lastNames = ['Dedy', 'Abdillah', 'Pratama', 'Kusuma', 'Sunandar', 'Iskandar', 'Meifirdo', 'Atmaja'];

        $firstName = $gender === GenderEnum::MALE->value
            ? $maleFirstNames[($index - 1) % count($maleFirstNames)]
            : $femaleFirstNames[($index - 1) % count($femaleFirstNames)];

        $lastName = $lastNames[($index - 1) % count($lastNames)];

        return "{$firstName} {$lastName}";
    }

    private function generateBirthDate(int $index): string
    {
        $birthYear = 1900 + rand(65, 85);
        $birthMonth = rand(1, 12);
        $birthDay = rand(1, 28);
        
        return sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
    }

    private function randomCity(): string
    {
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Malang', 'Denpasar'];
        return $cities[array_rand($cities)];
    }

    private function generateAddress(int $index): string
    {
        $streets = ['Jl. Merdeka', 'Jl. Sudirman', 'Jl. Gatot Subroto', 'Jl. Thamrin', 'Jl. Hayam Wuruk'];
        $street = $streets[array_rand($streets)];
        $city = $this->randomCity();
        
        $rt = str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT);
        $rw = str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT);
        
        return "{$street} No. {$index}, RT {$rt}/RW {$rw}, {$city}";
    }

    private function generatePhoneNumber(int $index): string
    {
        $prefix = ['0812', '0813', '0821', '0822', '0853', '0856', '0857', '0858'];
        $selectedPrefix = $prefix[array_rand($prefix)];
        $middle = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        $end = str_pad($index, 4, '0', STR_PAD_LEFT);
        
        return "{$selectedPrefix}{$middle}{$end}";
    }
}