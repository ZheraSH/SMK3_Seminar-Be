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

    private const DISTRIBUTION = [
        'HOMEROOM_TEACHER' => 9,
        'TEACHER_ONLY' => 12,
        'COUNSELOR' => 3,
        'STAFF' => 6,
    ];

    private $homeroomTeachers = [];
    private $regularTeachers = [];

    public function run(): void
    {
        $religion = Religion::where('name', 'Islam')->first();

        if (!$religion) {
            return;
        }

        $counter = 1;

        for ($i = 1; $i <= self::DISTRIBUTION['HOMEROOM_TEACHER']; $i++) {
            $employee = $this->createEmployee($counter, $religion, true);
            $this->homeroomTeachers[] = $employee->id;
            $counter++;
        }

        for ($i = 1; $i <= self::DISTRIBUTION['TEACHER_ONLY']; $i++) {
            $employee = $this->createEmployee($counter, $religion, false);
            $this->regularTeachers[] = $employee->id;
            $counter++;
        }

        for ($i = 1; $i <= self::DISTRIBUTION['COUNSELOR']; $i++) {
            $this->createEmployee($counter, $religion, false, RoleEnum::COUNSELOR->value);
            $counter++;
        }

        for ($i = 1; $i <= self::DISTRIBUTION['STAFF']; $i++) {
            $roles = ($i === 1) 
                ? [RoleEnum::STAFF->value, RoleEnum::CURRICULUM_COORDINATOR->value]
                : [RoleEnum::STAFF->value];
            
            $this->createEmployee($counter, $religion, false, $roles);
            $counter++;
        }
    }

    private function createEmployee(int $index, Religion $religion, bool $isHomeroom = false, $roles = null): Employee
    {
        $gender = $this->determineGender($index);
        $name = $this->generateName($index, $gender->value);
        $email = "employee{$index}@skaniga.com";
        $nip = $this->generateNIP($index);
        
        if ($roles === null) {
            $roles = $isHomeroom 
                ? [RoleEnum::TEACHER->value, RoleEnum::HOMEROOM_TEACHER->value]
                : [RoleEnum::TEACHER->value];
        } elseif (!is_array($roles)) {
            $roles = [$roles];
        }

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
                'nik' => $this->generateNIK($index),
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

    private function generateNIP(int $index): string
    {
        $year = 1980 + ($index % 20);
        $unique = str_pad($index, 10, '0', STR_PAD_LEFT);
        return "{$year}{$unique}";
    }

    private function generateNIK(int $index): string
    {
        $province = '32';
        $random = str_pad($index, 14, '0', STR_PAD_LEFT);
        return "{$province}{$random}";
    }

    private function generateBirthDate(int $index): string
    {
        $minAge = 25;
        $maxAge = 55;
        $age = $minAge + (($index - 1) % ($maxAge - $minAge + 1));
        return now()->subYears($age)->subMonths(rand(0, 11))->subDays(rand(0, 30))->format('Y-m-d');
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
        return "{$street} No. {$index}, {$city}";
    }

    private function generatePhoneNumber(int $index): string
    {
        $prefix = ['0812', '0813', '0821', '0822', '0853', '0856', '0857', '0858'];
        $selectedPrefix = $prefix[array_rand($prefix)];
        $number = str_pad($index, 8, '0', STR_PAD_LEFT);
        return "{$selectedPrefix}{$number}";
    }
}