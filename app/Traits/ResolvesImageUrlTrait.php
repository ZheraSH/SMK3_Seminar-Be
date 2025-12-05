<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ResolvesImageUrlTrait
{
    public function resolveImageUrl(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
    
        if (Str ::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, 'admin_assets')) {
            return asset($value);
        }

        return asset('storage/' . $value);
    }
    
}