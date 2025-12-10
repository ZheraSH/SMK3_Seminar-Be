<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'operator@skaniga.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Operator Sekolah',
                'slug' => Str::slug('Operator Sekolah'),
                'password' => Hash::make('developer'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([RoleEnum::SCHOOL->value]);
    }
}