<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Models\Religion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $religion = Religion::where('name', 'Islam')->first();

        for ($i = 1; $i <= 20; $i++) {
            $gender = $i % 2 == 0 ? GenderEnum::MALE : GenderEnum::FEMALE;
            
            $name = $this->generateName($i, $gender->value);
            $email = "student{$i}@skaniga.com";
            $nisn = '00' . str_pad($i, 10, '0', STR_PAD_LEFT);
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'id' => Str::uuid(),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'password' => Hash::make($nisn),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([RoleEnum::STUDENT->value]);

            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => $user->id,
                    'image' => $gender->value === GenderEnum::MALE->value 
                        ? 'default_image/student-boy.png'
                        : 'default_image/student-girl.png',
                    'nisn' => $nisn,
                    'religion_id' => $religion->id,
                    'gender' => $gender->value,
                    'birth_date' => now()->subYears(rand(15, 18))->format('Y-m-d'),
                    'birth_place' => $this->randomCity(),
                    'address' => 'Jl. Pelajar No.' . $i . ', Kota Contoh',
                    'number_kk' => '32' . str_pad($i, 14, '0', STR_PAD_LEFT),
                    'number_akta' => 'AK' . str_pad($i, 11, '0', STR_PAD_LEFT),
                    'order_child' => rand(1, 3),
                    'count_siblings' => rand(0, 4),
                ]
            );
        }
    }

    private function generateName(int $index, string $gender): string
    {
        $maleNames = ['Nando', 'Saiful', 'Fairouz', 'Dimas', 'Angga','Hilman', 'King', 'Ega', 'Zherash', 'Shinozaki'];
        $femaleNames = ['Dwi', 'Vita', 'Weis', 'Sekar', 'Rani','Edel', 'Alexia', 'Rara', 'Ai', 'Lovita'];
        $lastNames = ['Hamzi', 'Islami', 'Cairigio', 'Nayaka','Ramadhan', 'Rahmawati', 'Tirta'];
        
        $firstName = $gender === GenderEnum::MALE->value
            ? $maleNames[($index - 1) % count($maleNames)]
            : $femaleNames[($index - 1) % count($femaleNames)];
            
        $lastName = $lastNames[($index - 1) % count($lastNames)];
        
        return "{$firstName} {$lastName}";
    }

    private function randomCity(): string
    {
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakkarta', 'Semarang'];
        return $cities[array_rand($cities)];
    }
}