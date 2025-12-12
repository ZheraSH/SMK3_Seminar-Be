<?php

namespace App\Traits\Resources;

trait HasEnumLabelsTrait
{
    protected function getEnumLabel($enum, string $default = '-'): string
    {
        if (!$enum) {
            return $default;
        }

        if ($enum instanceof \BackedEnum) {
            return method_exists($enum, 'label') ? $enum->label() : $enum->value;
        }

        return $default;
    }

    protected function getEnumValue($enum): ?string
    {
        if (!$enum) {
            return null;
        }

        if ($enum instanceof \BackedEnum) {
            return $enum->value;
        }

        return (string) $enum;
    }
}
