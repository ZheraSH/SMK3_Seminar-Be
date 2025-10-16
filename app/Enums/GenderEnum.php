<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = "male";
    case FEMALE = "female";

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }
}
