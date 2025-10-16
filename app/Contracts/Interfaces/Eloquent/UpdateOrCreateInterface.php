<?php

namespace App\Contracts\Interfaces\Eloquent;

interface UpdateOrCreateInterface
{
    /**
     * Handle store data event to models.
     *
     * @param array $data
     *
     * @return mixed
     */

    public function updateOrCreate(array $match, array $data): mixed;
}
