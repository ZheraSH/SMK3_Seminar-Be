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
                'rfid' => '0000123456',
            ],
            [
                'rfid' => '0000654321',
            ],
            [
                'rfid' => '0000987654',
            ],
        ];

        foreach ($mastercards as $card) {
            \App\Models\Mastercard::create($card);
        }
    }
}
