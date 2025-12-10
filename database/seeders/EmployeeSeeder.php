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
    public function run(): void
    {
        $religion = Religion::where('name', 'Islam')->first();

        for ($i = 1; $i <= 20; $i++) {
            $gender = $i % 2 == 0 ? GenderEnum::MALE : GenderEnum::FEMALE;
            
            $name = $this->generateName($i, $gender->value);
            $email = "employee{$i}@skaniga.com";
            $nip = "1980" . str_pad($i, 10, '0', STR_PAD_LEFT);
            
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

            $user->syncRoles($this->determineRoles($i));

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => Str::uuid(),
                    'image' => $gender->value === GenderEnum::MALE->value 
                        ? 'default_image/teacher-boy.png'
                        : 'default_image/teacher-girl.png',
                    'NIP' => $nip,
                    'NIK' => '32' . str_pad($i, 14, '0', STR_PAD_LEFT),
                    'religion_id' => $religion->id,
                    'gender' => $gender->value,
                    'birth_date' => now()->subYears(rand(25, 50))->format('Y-m-d'),
                    'birth_place' => $this->randomCity(),
                    'address' => 'Jl. Contoh No.' . $i . ', Kota Contoh',
                    'phone_number' => '08' . str_pad($i, 10, '0', STR_PAD_LEFT),
                ]
            );
        }
    }

    private function generateName(int $index, string $gender): string
    {
        $maleNames = ['Tegar', 'Dimas', 'Firman', 'Sbastian', 'Valen', 'Ramzi', 'Gunawan', 'Nidal', 'Azadi', 'Jaka'];
        $femaleNames = ['Rofiatul', 'Rohmah', 'Inka', 'Putri', 'Ica', 'Riang', 'Nining', 'Niendy', 'Indah'];
        $lastNames = ['Dedy', 'Abdillah', 'Pratama', 'Kusuma', 'Sunandar', 'Iskandar', 'Meifirdo', 'Atmaja'];
        
        $firstName = $gender === GenderEnum::MALE->value
            ? $maleNames[($index - 1) % count($maleNames)]
            : $femaleNames[($index - 1) % count($femaleNames)];
            
        $lastName = $lastNames[($index - 1) % count($lastNames)];
        
        return "{$firstName} {$lastName}";
    }

    private function randomCity(): string
    {
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Malang', 'Bali'];
        return $cities[array_rand($cities)];
    }

    private function determineRoles(int $index): array
    {
        if ($index <= 2) return [RoleEnum::COUNSELOR->value];
        if ($index <= 4) return [RoleEnum::STAFF->value];
        if ($index <= 6) return [RoleEnum::CURRICULUM_COORDINATOR->value];
        if ($index <= 10) return [RoleEnum::HOMEROOM_TEACHER->value];
        
        return [RoleEnum::TEACHER->value];
    }
}