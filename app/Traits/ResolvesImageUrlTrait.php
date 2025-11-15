<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ResolvesImageUrlTrait
{
    /**
     * Resolve image URL with fallback
     */
    protected function resolveImageUrl(?string $photo, string $defaultImage = 'admin_assets/dist/image/profile/default.jpg'): string
    {
        if (!$photo) {
            return asset($defaultImage);
        }

        if (Storage::disk('public')->exists($photo)) {
            return url('storage/' . $photo);
        }

        if (file_exists(public_path($photo))) {
            return asset($photo);
        }

        return asset($defaultImage);
    }
}

