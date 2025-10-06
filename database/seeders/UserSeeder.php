<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel users dulu (reset auto increment juga)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Admin Sekolah
        $admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('operator sekolah');

        // Dummy Staff TU
        $staff = User::create([
            'name' => 'Staff Tata Usaha',
            'email' => 'staff.tu@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff TU');

        // Dummy Guru Matematika
        $guru1 = User::create([
            'name' => 'Guru Matematika',
            'email' => 'guru.matematika@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $guru1->assignRole('guru');

        // Dummy Guru Bahasa Inggris
        $guru2 = User::create([
            'name' => 'Guru Bahasa Inggris',
            'email' => 'guru.bahasainggris@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $guru2->assignRole('guru');

        // Dummy Siswa 1
        $siswa1 = User::create([
            'name' => 'Siswa Pertama',
            'email' => 'siswa1@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $siswa1->assignRole('siswa');

        // Dummy Siswa 2
        $siswa2 = User::create([
            'name' => 'Siswa Kedua',
            'email' => 'siswa2@sekolah.com',
            'password' => Hash::make('password'),
        ]);
        $siswa2->assignRole('siswa');
    }
}