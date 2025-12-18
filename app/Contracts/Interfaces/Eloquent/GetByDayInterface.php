<?php

namespace App\Contracts\Interfaces\Eloquent;

interface GetByDayInterface
{
    public function getByDay(string $day): mixed;
}
