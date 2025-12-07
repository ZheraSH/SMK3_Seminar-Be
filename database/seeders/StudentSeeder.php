<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\User;
use App\Models\Religion;
use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        Role::firstOrCreate(['name' => RoleEnum::STUDENT->value]);

        $religion = Religion::firstOrCreate(
            ['name' => 'Islam'],
            ['id' => (string) Str::uuid()]
        );

        $imageMale = 'default_image/student-boy.png';
        $imageFemale = 'default_image/student-girl.png';

        for ($i = 1; $i <= 20; $i++) {

            $gender = $faker->randomElement([
                GenderEnum::MALE->value,
                GenderEnum::FEMALE->value
            ]);

            $name = $this->generateRandomName($faker, $gender);
            $email = "siswa{$i}@skaniga.com";
            $nisn = '99' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'password' => Hash::make($nisn),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([RoleEnum::STUDENT->value]);

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => $user->id,
                    'image' => $gender === GenderEnum::MALE->value ? $imageMale : $imageFemale,
                    'nisn' => $nisn,
                    'religion_id' => $religion->id,
                    'gender' => $gender,
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

    private function generateRandomName($faker, string $gender): string
    {
        $maleFirstNames = ['Nando', 'Saiful', 'Fairouz', 'Dimas', 'Angga','Hilman', 'King', 'Ega', 'Zherash', 'Shinozaki'];
        $femaleFirstNames = ['Dwi', 'Vita', 'Weis', 'Sekar', 'Rani','Edel', 'Alexia', 'Rara', 'Ai', 'Lovita'];
        $lastNames = ['Hamzi', 'Islami', 'Cairigio', 'Nayaka','Ramadhan', 'Rahmawati', 'Tirta'];

        $first = $gender === GenderEnum::MALE->value
            ? $faker->randomElement($maleFirstNames)
            : $faker->randomElement($femaleFirstNames);

        $last = $faker->randomElement($lastNames);

        return "{$first} {$last}";
    }
}