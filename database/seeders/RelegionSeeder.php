<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Religion;

class RelegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu',];

        foreach ($religions as $name) {
            Religion::firstOrCreate(['name' => $name]);
        }

    }
}
