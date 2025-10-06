<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and sub roles
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            RelegionSeeder::class,
            SubRoleSeeder::class,
            StudentSeeder::class,
            SchoolYearSeeder::class,
            LevelClassSeeder::class,
            MapelSeeder::class,
        ]);

        // Create 4 default users with specific IDs
        $users = [
            [
                'id' => 1,
                'name' => 'Admin Sekolah',
                'email' => 'admin@sekolah.com',
                'password' => bcrypt('password123'),
                'gender' => 'male',
                'role' => 'operator sekolah',
                'sub_role' => null,
            ],
            [
                'id' => 2,
                'name' => 'Siswa',
                'email' => 'siswa@sekolah.com',
                'password' => bcrypt('password123'),
                'gender' => 'male',
                'role' => 'siswa',
                'sub_role' => null,
            ],
            [
                'id' => 3,
                'name' => 'Staff TU',
                'email' => 'staff@sekolah.com',
                'password' => bcrypt('password123'),
                'gender' => 'male',
                'role' => 'staff TU',
                'sub_role' => null,
            ],
            [
                'id' => 4,
                'name' => 'Guru Pengajar',
                'email' => 'guru@sekolah.com',
                'password' => bcrypt('password123'),
                'gender' => 'female',
                'role' => 'guru',
                'sub_role' => 'guru pengajar',
            ],
        ];

        foreach ($users as $userData) {
            // Create user with specific ID
            $user = User::UpdateOrCreate([
                'id' => $userData['id'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            // Assign role
            $role = \App\Models\Role::where('name', $userData['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id, ['assigned_by' => 1]); // Admin assigns all roles
            }

            // Assign sub role if exists
            if ($userData['sub_role']) {
                $subRole = \App\Models\SubRole::where('name', $userData['sub_role'])->first();
                if ($subRole) {
                    $user->subRoles()->attach($subRole->id, ['assigned_by' => 1]); // Admin assigns all sub roles
                }
            }
        }
    }
}
