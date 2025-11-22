<?php

namespace App\Enums;

enum TapStatusEnum: string
{
    case VALID = 'valid';
    case INVALID = 'tidak_valid';
    case DUPLICATE = 'duplicate';
    case BEFORE_TIME = 'sebelum_jam_masuk';
    case AFTER_TIME = 'setelah_jam_masuk';
    
    public function label(): string
    {
        return match($this) {
            self::VALID => 'Valid',
            self::INVALID => 'Tidak Valid',
            self::DUPLICATE => 'Duplicate Tap',
            self::BEFORE_TIME => 'Sebelum Jam Masuk',
            self::AFTER_TIME => 'Setelah Jam Masuk',
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