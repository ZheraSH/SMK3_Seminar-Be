<?php

namespace App\Enums;

enum AttendanceProofEnum: string
{
    case RFID = 'rfid';
    case MANUAL = 'manual';
    case CLASSROOM = 'class';

    public function label(): string
    {
        return match($this) {
            self::RFID => 'Kartu RFID',
            self::MANUAL => 'Input Manual',
            self::CLASSROOM => 'Absen Kelas',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }
}