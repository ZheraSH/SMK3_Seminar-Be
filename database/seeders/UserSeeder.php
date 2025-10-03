<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // foreach (Role::all() as $role) {
        //     $user = User::create([
        //         'name' => $role->name,
        //         'slug' => Str::slug($role->name),
        //         'email' => str_replace(' ', '', strtolower($role->name)) . "@gmail.com",
        //         'email_verified_at' => now(),
        //         'password' => bcrypt('password'),
        //     ]);

        //     // Assign role ke user
        //     $user->assignRole($role);

        //     // Jika role = staff, tambahkan permission khusus
        //     if ($role->name === 'staff') {
        //         $user->givePermissionTo('view_violation');
        //     }
        // }
    }
}
