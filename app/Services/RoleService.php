<?php

namespace App\Services;

use App\Contracts\Interfaces\RoleInterface;

class RoleService
{
    private RoleInterface $roleInterface;

    public function __construct(RoleInterface $roleInterface)
    {
        $this->roleInterface = $roleInterface;
    }

    public function get(): mixed
    {
        return $this->roleInterface->get();
    }
}