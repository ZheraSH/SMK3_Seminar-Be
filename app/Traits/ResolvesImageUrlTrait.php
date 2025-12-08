<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ResolvesImageUrlTrait
{
    protected function resolveImageUrl(?string $path, string $default = 'admin_assets/dist/image/profile/default.jpg'): string
    {
        if (!$path) {
            return asset($default);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Jika file ada di storage/public
        if (Storage::disk('public')->exists($path)) {
            return asset("storage/" . $path);
        }

        // Jika file ada di public/
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset($default);
    }
}