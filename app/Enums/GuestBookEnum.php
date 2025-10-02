<?php

namespace App\Enums;

enum GuestBookEnum: string
{
    case NEGERI = "negeri";
    case SWASTA = "swasta";
    case INDIVIDUAL = "individual";

    public function color(): string
    {
        return match ($this) {
            self::NEGERI => 'success',
            self::SWASTA => 'warning',
            self::INDIVIDUAL => 'primary',
        };
    }
}
