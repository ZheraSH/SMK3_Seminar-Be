<?php

namespace App\Contracts\Interfaces\Eloquent;

interface WhereInterface
{
    /**
     * Handle show method and update data instantly from models.
     *
     * @param mixed $data
     *
     * @return mixed
     */

    public function where(mixed $data): mixed;
}
