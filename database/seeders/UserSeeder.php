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

            // Assign Spatie role by name if exists
            if (!empty($userData['role'])) {
                try {
                    $user->assignRole($userData['role']);
                } catch (\Throwable $e) {
                   
                }
            }
        }
        foreach (Role::all() as $role) {
            $email = str_replace(' ', '', $role->name) . '@gmail.com';
            $user = User::create([
                'name' => ucfirst($role->name),
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);
            $user->assignRole($role);
        }
    }
}
