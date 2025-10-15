<?php

namespace App\Contracts\Interfaces\Eloquent;

interface PaginateInterface
{
    /**
     * Handle the Get all data event from models.
     *
     * @return mixed
     */

    public function paginate(): mixed;
}
