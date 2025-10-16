<?php

namespace App\Contracts\Interfaces\Eloquent;

interface ShowWithSlugInterface
{
    /**
     * Handle get the specified data by slug from models.
     *
     * @param string $slug
     *
     * @return mixed
     */

    public function showWithSlug(string $slug): mixed;
}
