<?php

namespace App\Services;

use App\Contracts\Interfaces\RoleInterface;

class RoleService
{
    private RoleInterface $roleRepository;

    public function __construct(RoleInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get all roles for dropdown
     */
    public function getAllRoles(): mixed
    {
        return $this->roleRepository->get();
    }
}