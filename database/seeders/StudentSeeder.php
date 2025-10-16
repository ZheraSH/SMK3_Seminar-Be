<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\User;
use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        Role::firstOrCreate(['name' => RoleEnum::STUDENT->value]);

        for ($i = 1; $i <= 2; $i++) {
            $name = "Siswa {$i}";
            $email = "siswa{$i}@Skaniga.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'password' => Hash::make('murid123'),
                ]
            );

            $user->syncRoles([RoleEnum::STUDENT->value]);

            $nisn = '99' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'image' => null,
                    'nisn' => $nisn,
                    'religion_id' => \App\Models\Religion::first()->id,
                    'gender' => $faker->randomElement([
                        GenderEnum::MALE->value,
                        GenderEnum::FEMALE->value
                    ]),
                    'birth_date' => $faker->dateTimeBetween('-17 years', '-15 years')->format('Y-m-d'),
                    'birth_place' => $faker->city(),
                    'address' => $faker->address(),
                    'number_kk' => (string) $faker->numerify('################'),
                    'number_akta' => (string) $faker->numerify('#############'),
                    'order_child' => $faker->numberBetween(1, 5),
                    'count_siblings' => $faker->numberBetween(0, 6),
                ]
            );
        }
    }
}

//Done