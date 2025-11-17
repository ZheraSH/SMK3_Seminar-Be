<?php

namespace App\Enums;

enum AttendanceProofEnum: string
{
    case RFID = 'rfid';
    case MANUAL = 'manual';
    case CLASSROOM = 'class';
    case ONLINE = 'online';

    public function label(): string
    {
        return match($this) {
            self::RFID => 'Kartu RFID',
            self::MANUAL => 'Input Manual',
            self::CLASSROOM=> 'Absen Kelas',
            self::ONLINE => 'Online',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}