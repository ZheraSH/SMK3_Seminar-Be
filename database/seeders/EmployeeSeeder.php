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

        // Hanya buat role untuk employee saja
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

        // Data dummy employees
        $employees = [
            // Guru biasa
            [
                'name' => 'Guru Matematika',
                'email' => 'guru.matematika@skaniga.com',
                'roles' => [RoleEnum::TEACHER->value],
                'NIP' => '19800000000001',
                'gender' => GenderEnum::MALE->value,
            ],
            [
                'name' => 'Guru Bahasa',
                'email' => 'guru.bahasa@skaniga.com', 
                'roles' => [RoleEnum::TEACHER->value],
                'NIP' => '19800000000002',
                'gender' => GenderEnum::FEMALE->value,
            ],
            
            // Staff TU
            [
                'name' => 'Staff Tu',
                'email' => 'staff.administrasi@skaniga.com',
                'roles' => [RoleEnum::STAFF->value],
                'NIP' => '19900000000001',
                'gender' => GenderEnum::FEMALE->value,
            ],
            
            // Wali Kelas (multiple roles)
            [
                'name' => 'Wali Kelas',
                'email' => 'wali.kelas.x@skaniga.com',
                'roles' => [RoleEnum::TEACHER->value, RoleEnum::HOMEROOM_TEACHER->value],
                'NIP' => '19750000000001',
                'gender' => GenderEnum::MALE->value,
            ],
            
            // Guru BK (multiple roles)  
            [
                'name' => 'BK',
                'email' => 'guru.bimbingan@skaniga.com',
                'roles' => [RoleEnum::TEACHER->value, RoleEnum::COUNSELOR->value],
                'NIP' => '19850000000001',
                'gender' => GenderEnum::FEMALE->value,
            ],
            
            // Waka Kurikulum (multiple roles)
            [
                'name' => 'Waka Kurikulum',
                'email' => 'waka.kurikulum@skaniga.com',
                'roles' => [RoleEnum::TEACHER->value, RoleEnum::CURRICULUM_COORDINATOR->value],
                'NIP' => '19700000000001',
                'gender' => GenderEnum::MALE->value,
            ],
        ];

        foreach ($employees as $employeeData) {
            $user = User::updateOrCreate(
                ['email' => $employeeData['email']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $employeeData['name'],
                    'slug' => Str::slug($employeeData['name']),
                    'password' => Hash::make('employee123'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles($employeeData['roles']);

            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => (string) Str::uuid(),
                    'image' => null,
                    'NIP' => $employeeData['NIP'],
                    'NIK' => $faker->unique()->numerify('################'),
                    'religion_id' => $religion->id,
                    'gender' => $employeeData['gender'],
                    'birth_date' => $faker->dateTimeBetween('-45 years', '-25 years')->format('Y-m-d'),
                    'birth_place' => $faker->city(),
                    'address' => $faker->address(),
                    'phone_number' => $faker->phoneNumber(),
                ]
            );
        }
    }
}