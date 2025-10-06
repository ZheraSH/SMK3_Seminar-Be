<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Role::all() as $role) {
            $email = str_replace(' ', '', $role->name) . '@example.com';
            $user = User::create([
                'name' => ucfirst($role->name),
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);
            $user->assignRole($role);
        }
    }
}
