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
        $operators = [
            ['email' => 'Developer@skaniga.com', 'name' => 'Developer', 'password' => 'D3veL0p3R'],
            ['email' => 'operator1@skaniga.com', 'name' => 'Operator 1', 'password' => 'operator1'],
            ['email' => 'operator2@skaniga.com', 'name' => 'Operator 2', 'password' => 'operator2'],
            ['email' => 'operator3@skaniga.com', 'name' => 'Operator 3', 'password' => 'operator3'],
        ];

        foreach ($operators as $op) {
            $user = User::firstOrCreate(
                ['email' => $op['email']],
                [
                    'id' => Str::uuid(),
                    'name' => $op['name'],
                    'slug' => Str::slug($op['name']),
                    'password' => Hash::make($op['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([RoleEnum::SCHOOL->value]);
        }
    }
}
