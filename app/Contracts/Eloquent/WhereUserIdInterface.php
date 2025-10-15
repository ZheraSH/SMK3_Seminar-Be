<?php

namespace App\Contracts\Interfaces\Eloquent;

interface WhereUserIdInterface
{
    /**
     * Handle get data where user_id from models.
     *
     * @param mixed $id
     *
     * @return mixed
     */

    public function whereUserId(mixed $id): mixed;
}
