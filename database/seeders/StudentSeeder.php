<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Enums\GenderEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        for ($i = 1; $i <= 6; $i++) {
            $name = 'Siswa ' . $i;
            $email = 'siswa' . $i . '@Skaniga.com';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);

            try {
                $user->assignRole('siswa');
            } catch (\Throwable $e) {
                // role may not exist yet; ignore
            }

            $nisn = '99' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);
            $nik = '35' . str_pad((string) $i, 14, '0', STR_PAD_LEFT);

            Student::create([
                'user_id' => $user->id,
                'image' => null,
                'nisn' => $nisn,
                'religion_id' => $faker->numberBetween(1, 5),
                'gender' => $faker->randomElement([GenderEnum::MALE->value, GenderEnum::FEMALE->value]),
                'birth_date' => $faker->dateTimeBetween('-17 years', '-15 years')->format('Y-m-d'),
                'birth_place' => $faker->city(),
                'address' => $faker->address(),
                'nik' => $nik,
                'number_kk' => (string) $faker->numerify('################'),
                'number_birth_certificate' => (string) $faker->numerify('#############'),
                'order_child' => $faker->numberBetween(1, 5),
                'count_siblings' => $faker->numberBetween(0, 6),
                'point' => $faker->numberBetween(0, 100),
            ]);
        }
    }
}
