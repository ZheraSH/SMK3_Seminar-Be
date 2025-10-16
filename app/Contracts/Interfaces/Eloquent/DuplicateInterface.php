<?php

namespace App\Contracts\Interfaces\Eloquent;

interface DuplicateInterface
{
    /**
     * Handle show method and update data instantly from models.
     *
     * @param mixed $data
     *
     * @return mixed
     */

    public function duplicate(mixed $data): mixed;
}
