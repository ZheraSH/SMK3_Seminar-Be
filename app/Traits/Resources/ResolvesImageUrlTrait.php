<?php

namespace App\Traits\Resources;

use Illuminate\Support\Facades\Storage;

trait ResolvesImageUrlTrait
{
    protected function resolveImageUrl(?string $path, string $default = 'storage/default_image/default.png'): string
    {
        if (!$path) {
            return asset($default);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset("storage/" . $path);
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset($default);
    }
}