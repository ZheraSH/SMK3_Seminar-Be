<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'operator sekolah',
                'description' => 'Admin sekolah dengan akses penuh ke sistem',
            ],
            [
                'name' => 'siswa',
                'description' => 'Siswa sekolah',
            ],
            [
                'name' => 'staff TU',
                'description' => 'Staff Tata Usaha',
            ],
            [
                'name' => 'guru',
                'description' => 'Guru dengan berbagai sub role',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                [
                    'name' => $role['name'],
                    'guard_name' => 'web', // WAJIB untuk Spatie
                ],
                [
                    'description' => $role['description'] ?? null,
                ]
            );
        }
    }
}
