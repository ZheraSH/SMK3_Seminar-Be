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

        Role::firstOrCreate(['name' => RoleEnum::TEACHER->value]);
        // Role::firstOrCreate(['name' => RoleEnum::STAFF->value]);

        $religion = Religion::firstOrCreate(
            ['name' => 'Islam'],
            ['id' => (string) Str::uuid()]
        );

        $employeesData = [
            ['role' => RoleEnum::TEACHER->value, 'prefix' => 'Guru', 'nip_start' => '1980', 'count' => 2],
            // ['role' => RoleEnum::STAFF->value, 'prefix' => 'Staff TU', 'nip_start' => '1990', 'count' => 2],
        ];

        foreach ($employeesData as $data) {
            for ($i = 1; $i <= $data['count']; $i++) {
                $name = "{$data['prefix']} {$i}";
                $email = strtolower(str_replace(' ', '', $name)) . '@skaniga.com';

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'id' => (String) Str::uuid(),
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'password' => Hash::make('employee123'),
                        'email_verified_at' => now(),
                    ]
                );

                $user->syncRoles([$data['role']]);

                $employeeId = $user->id;

                Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'id' => $employeeId,
                        'image' => null,
                        'NIP' => $data['nip_start'] . str_pad($i, 10, '0', STR_PAD_LEFT),
                        'NIK' => (string) $faker->numerify('################'),
                        'religion_id' => $religion->id,
                        'gender' => $faker->randomElement([
                            GenderEnum::MALE->value,
                            GenderEnum::FEMALE->value,
                        ]),
                        'birth_date' => $faker->dateTimeBetween(
                            $data['role'] === RoleEnum::TEACHER->value ? '-45 years' : '-40 years',
                            $data['role'] === RoleEnum::TEACHER->value ? '-30 years' : '-25 years'
                        )->format('Y-m-d'),
                        'birth_place' => $faker->city(),
                        'address' => $faker->address(),
                        'phone_number' => $faker->phoneNumber(),
                        'active' => true,
                    ]
                );
            }
        }
    }
}
