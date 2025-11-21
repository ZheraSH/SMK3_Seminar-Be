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
        $role = Role::firstOrCreate(
            ['name' => RoleEnum::SCHOOL->value],
            ['guard_name' => 'web']
        );

        $user = User::updateOrCreate(
            ['email' => 'operatorsekolah@skaniga.com'],
            [
                'id' => Str::uuid(),
                'name' => RoleEnum::SCHOOL->label(),
                'slug' => Str::slug(RoleEnum::SCHOOL->value),
                'email_verified_at' => now(),
                'password' => Hash::make('developer'),
            ]
        );

        $user->syncRoles([$role->name]);
    }
}
