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

            $email = match ($enumRole) {
                RoleEnum::SCHOOL => 'operatorsekolah@skaniga.com',
                RoleEnum::TEACHER => 'gurupengajar@skaniga.com',
                RoleEnum::STUDENT => 'siswa@skaniga.com',
                RoleEnum::STAFF => 'stafftu@skaniga.com',
                RoleEnum::HOMEROOM_TEACHER => 'walikelas@skaniga.com',
                RoleEnum::COUNSELOR => 'gurubk@skaniga.com',
                RoleEnum::CURRICULUM_COORDINATOR => 'wakakurikulum@skaniga.com',
            };

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'id' => Str::uuid(),
                    'name' => ucwords(str_replace('_', ' ', $enumRole->value)),
                    'slug' => Str::slug($enumRole->value),
                    'email_verified_at' => now(),
                    'password' => Hash::make('developer'),
                ]
            );

            $user->syncRoles([$role->name]);
        }

        // Create user with multiple roles as example
        $multiRoleUser = User::updateOrCreate(
            ['email' => 'multirole@skaniga.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Multi Role User',
                'slug' => 'multi-role-user',
                'email_verified_at' => now(),
                'password' => Hash::make('developer'),
            ]
        );

        $multiRoleUser->syncRoles([
            RoleEnum::TEACHER->value,
            RoleEnum::HOMEROOM_TEACHER->value,
            RoleEnum::CURRICULUM_COORDINATOR->value
        ]);
    }
}