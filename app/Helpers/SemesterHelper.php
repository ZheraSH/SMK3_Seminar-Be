<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log; 

class SemesterHelper
{
    public static function getSemester(): array
    {
        $month = intval(now()->format('n'));
        $data = [];

        if ($month >= 7 && $month <= 12) {
            $data = [
                'semester' => 'Ganjil',
                'month' => [7, 8, 9, 10, 11, 12]
            ];
        } else {
            $data = [
                'semester' => 'Genap',
                'month' => [1, 2, 3, 4, 5, 6]
            ];
        }

        return $data;
    }

    public static function commitSemester(): void
    {
        $semester = self::getSemester();
        Log::info("Semester aktif saat ini: {$semester['semester']} (bulan: ".implode(',', $semester['month']).")");
    }
}
