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

        $this->createRoles();

        $religion = Religion::firstOrCreate(
            ['name' => 'Islam'],
            ['id' => (string) Str::uuid()]
        );

        $imageMale = 'default_image/teacher-boy.png';
        $imageFemale = 'default_image/teacher-girl.png';

        $teacherCount = 0;
        $homeroomCount = 0;
        $counselorCount = 0;
        $curriculumCount = 0;
        $staffCount = 0;

        for ($i = 1; $i <= 20; $i++) {
            $gender = $faker->randomElement([
                GenderEnum::MALE->value,
                GenderEnum::FEMALE->value
            ]);

            $name = $this->generateRandomName($faker, $gender);
            $email = "employee{$i}@skaniga.com";
            $nip = $this->generateNIP($i);

            $roles = $this->determineRoles(
                $i, 
                $teacherCount, 
                $homeroomCount, 
                $counselorCount, 
                $curriculumCount, 
                $staffCount
            );

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'password' => Hash::make($nip),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles($roles);

            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id' => (string) Str::uuid(),
                    'image' => $gender === GenderEnum::MALE->value ? $imageMale : $imageFemale,
                    'NIP' => $nip,
                    'NIK' => $faker->unique()->numerify('################'),
                    'religion_id' => $religion->id,
                    'gender' => $gender,
                    'birth_date' => $faker->date('Y-m-d'),
                    'birth_place' => $faker->city(),
                    'address' => $faker->address(),
                    'phone_number' => $faker->phoneNumber(),
                ]
            );

            $this->updateCounters($roles, $teacherCount, $homeroomCount, $counselorCount, $curriculumCount, $staffCount);
        }
    }

    private function createRoles(): void
    {
        $roles = [
            RoleEnum::TEACHER->value,
            RoleEnum::HOMEROOM_TEACHER->value,
            RoleEnum::COUNSELOR->value,
            RoleEnum::STAFF->value,
            RoleEnum::CURRICULUM_COORDINATOR->value,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    private function determineRoles(
        int $index, 
        int &$teacherCount, 
        int &$homeroomCount, 
        int &$counselorCount, 
        int &$curriculumCount, 
        int &$staffCount
    ): array {

        $maxTeachers = 10;
        $maxHomeroom = 10;
        $maxCounselor = 2;
        $maxCurriculum = 2;
        $maxStaff = 2;

        // BK - role khusus, tidak bisa menjadi teacher
        if ($counselorCount < $maxCounselor && $index % 4 == 0) {
            $counselorCount++;
            return [RoleEnum::COUNSELOR->value];
        }

        if ($staffCount < $maxStaff && $index % 2 == 0) {
            $staffCount++;
            return [RoleEnum::STAFF->value];
        }

        if ($curriculumCount < $maxCurriculum && $index % 4 == 0) {
            $curriculumCount++;
            $teacherCount++;
            return [RoleEnum::TEACHER->value, RoleEnum::CURRICULUM_COORDINATOR->value];
        }

        if ($homeroomCount < $maxHomeroom && $index % 8 == 0) {
            $homeroomCount++;
            $teacherCount++;
            return [RoleEnum::TEACHER->value, RoleEnum::HOMEROOM_TEACHER->value];
        }

        if ($teacherCount < $maxTeachers) {
            $teacherCount++;
            return [RoleEnum::TEACHER->value];
        }

        $staffCount++;
        return [RoleEnum::STAFF->value];
    }

    private function updateCounters(
        array $roles, 
        int &$teacherCount, 
        int &$homeroomCount, 
        int &$counselorCount, 
        int &$curriculumCount, 
        int &$staffCount
    ): void {
        foreach ($roles as $role) {
            switch ($role) {
                case RoleEnum::TEACHER->value:
                    $teacherCount++;
                    break;
                case RoleEnum::HOMEROOM_TEACHER->value:
                    $homeroomCount++;
                    break;
                case RoleEnum::COUNSELOR->value:
                    $counselorCount++;
                    break;
                case RoleEnum::CURRICULUM_COORDINATOR->value:
                    $curriculumCount++;
                    break;
                case RoleEnum::STAFF->value:
                    $staffCount++;
                    break;
            }
        }
    }

    private function generateRandomName($faker, string $gender): string
    {
        $maleFirstNames = ['Tegar', 'Dimas', 'Firman', 'Sbastian', 'Valen', 'Ramzi', 'Gunawan', 'Nidal', 'Azadi', 'Jaka'];
        $femaleFirstNames = ['Rofiatul', 'Rohmah', 'Inka', 'Putri', 'Ica', 'Riang', 'Nining', 'Niendy', 'Indah'];
        $lastNames = ['Dedy', 'Abdillah', 'Pratama', 'Kusuma', 'Sunandar', 'Iskandar', 'Meifirdo', 'Atmaja'];

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