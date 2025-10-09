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
                'email' => 'admin@Skaniga.com',
                'password' => bcrypt('operator'),
                'gender' => 'male',
                'role' => 'operator sekolah',
            ],
            [
                'id' => 2,
                'name' => 'Guru Pengajar',
                'email' => 'guru@Skaniga.com',
                'password' => bcrypt('guru123'),
                'gender' => 'female',
                'role' => 'guru',
            ],
            [
                'id' => 3,
                'name' => 'Siswa',
                'email' => 'siswa@Skaniga.com',
                'password' => bcrypt('murid123'),
                'gender' => 'male',
                'role' => 'siswa',
            ],
            [
                'id' => 4,
                'name' => 'Staff TU',
                'email' => 'staff@Skaniga.com',
                'password' => bcrypt('stafftu'),
                'gender' => 'male',
                'role' => 'staff TU',
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
            $email = str_replace(' ', '', $role->name) . '@Skaniga.com';
            $user = User::create([
                'name' => ucfirst($role->name),
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);
            $user->assignRole($role);
        }
    }
}
