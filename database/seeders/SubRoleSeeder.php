<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubRole;
use App\Models\Role;

class SubRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs
        $guruRole = Role::where('name', 'guru')->first();

        $subRoles = [
            // Guru sub roles only
            [
                'name' => 'guru pengajar',
                'description' => 'Guru pengajar yang mengajar mata pelajaran',
                'role_id' => $guruRole->id,
                'is_default' => true,
            ],
            [
                'name' => 'wali kelas',
                'description' => 'Guru yang menjadi wali kelas',
                'role_id' => $guruRole->id,
                'is_default' => false,
            ],
            [
                'name' => 'Waka Kurikulum',
                'description' => 'Guru yang menjadi Wakil Kepala Sekolah Di Bidang Kurikulum',
                'role_id' => $guruRole->id,
                'is_default' => false,
            ],
            [
                'name' => 'bk',
                'description' => 'Guru yang menjadi Pengurus Bimbingan dan Konseling',
                'role_id' => $guruRole->id,
                'is_default' => false,
            ],
        ];

        foreach ($subRoles as $subRole) {
            SubRole::firstOrCreate(
                ['name' => $subRole['name'], 'role_id' => $subRole['role_id']],
                $subRole
            );
        }
    }
}