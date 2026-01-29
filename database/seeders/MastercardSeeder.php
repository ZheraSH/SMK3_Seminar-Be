<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MastercardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mastercards = [
            [
                'name' => 'Tablet Admin 1',
                'email' => 'admin.tablet1@smkn3pamekasan.sch.id',
                'rfid' => '0000123456',
            ],
            [
                'name' => 'Tablet Admin 2',
                'email' => 'admin.tablet2@smkn3pamekasan.sch.id',
                'rfid' => '0000654321',
            ],
            [
                'name' => 'Tablet Cadangan',
                'email' => 'admin.cadangan@smkn3pamekasan.sch.id',
                'rfid' => '0000987654',
            ],
        ];

        foreach ($mastercards as $card) {
            \App\Models\Mastercard::create($card);
        }
    }
}
