<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Employee;
use App\Models\Religion;
use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $employeeRoles = [
            RoleEnum::TEACHER->value,
            RoleEnum::STAFF->value, 
            RoleEnum::HOMEROOM_TEACHER->value,
            RoleEnum::COUNSELOR->value,
            RoleEnum::CURRICULUM_COORDINATOR->value,
        ];

        foreach ($employeeRoles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $religion = Religion::firstOrCreate(
            ['name' => 'Islam'],
            ['id' => (string) Str::uuid()]
        );

        for ($i = 1; $i <= 17; $i++) {
            $gender = $faker->randomElement([GenderEnum::MALE->value, GenderEnum::FEMALE->value]);
            $name = $this->generateRandomName($faker, $gender);
            $email = "employee{$i}@skaniga.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'password' => Hash::make('employee123'),
                    'email_verified_at' => now(),
                ]
            );

            $roleCount = $faker->numberBetween(1, 2);
            $roles = $faker->randomElements($employeeRoles, $roleCount);
            
            $user->syncRoles($roles);

            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => (string) Str::uuid(),
                    'image' => null,
                    'NIP' => $this->generateNIP($i),
                    'NIK' => $faker->unique()->numerify('################'),
                    'religion_id' => $religion->id,
                    'gender' => $gender,
                    'birth_date' => $faker->dateTimeBetween('-45 years', '-25 years')->format('Y-m-d'),
                    'birth_place' => $faker->city(),
                    'address' => $faker->address(),
                    'phone_number' => $faker->phoneNumber(),
                ]
            );
        }
    }

    private function generateRandomName($faker, string $gender): string
    {
        $maleFirstNames = ['Ahmad', 'Budi', 'Cahyo', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hadi', 'Irfan', 'Joko'];
        $femaleFirstNames = ['Ani', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hani', 'Indah', 'Juli'];
        $lastNames = ['Santoso', 'Wijaya', 'Pratama', 'Kusuma', 'Setiawan', 'Hidayat', 'Nugroho', 'Saputra'];
        
        $firstName = $gender === GenderEnum::MALE->value 
            ? $faker->randomElement($maleFirstNames)
            : $faker->randomElement($femaleFirstNames);
            
        $lastName = $faker->randomElement($lastNames);
        
        return "{$firstName} {$lastName}";
    }

    private function generateNIP(int $index): string
    {
        $baseYear = 19800000000000;
        return (string) ($baseYear + $index);
    }
}