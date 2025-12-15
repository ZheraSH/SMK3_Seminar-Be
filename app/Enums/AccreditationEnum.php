<?php

namespace App\Enums;

enum AccreditationEnum: string
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case NOT_ACCREDITED = 'not_accredited';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A',
            self::B => 'B',
            self::C => 'C',
            self::NOT_ACCREDITED => 'Belum Terakreditasi',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $data = [];
        foreach (self::cases() as $case) {
            $data[$case->value] = $case->label();
        }

        return $data;
    }
}
