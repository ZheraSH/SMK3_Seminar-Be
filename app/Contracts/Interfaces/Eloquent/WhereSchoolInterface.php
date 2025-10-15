<?php

namespace App\Contracts\Interfaces\Eloquent;

interface WhereSchoolInterface
{
    /**
     * Handle get data where user_id from models.
     *
     * @param mixed $id
     *
     * @return mixed
     */

    public function whereSchool(mixed $id): mixed;
}
