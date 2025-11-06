<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::cases() as $enumRole) {

            $role = Role::firstOrCreate(
                ['name' => $enumRole->value],
                ['guard_name' => 'web']
            );

            $baseEmail = match ($enumRole) {
                RoleEnum::SCHOOL => 'operatorsekolah',
                RoleEnum::STUDENT => 'siswa',
                RoleEnum::TEACHER => 'guru',
                RoleEnum::HOMEROOM_TEACHER => 'walikelas',
                RoleEnum::COUNSELOR => 'bk',
                RoleEnum::STAFF => 'stafftu',
                RoleEnum::CURRICULUM_COORDINATOR => 'wakakurikulum',
            };

            $email = "{$baseEmail}@skaniga.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'id' => Str::uuid(),
                    'name' => $enumRole->label(),
                    'slug' => Str::slug($enumRole->value),
                    'email_verified_at' => now(),
                    'password' => Hash::make('developer'),
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
}