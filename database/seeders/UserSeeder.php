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
                RoleEnum::SCHOOL => 'operatorschool@skaniga.com',
                RoleEnum::TEACHER => 'teacher@skaniga.com',
                RoleEnum::STUDENT => 'student@skaniga.com',
                RoleEnum::STAFF => 'stafftu@skaniga.com',
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
    }
}
