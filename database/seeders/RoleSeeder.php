<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate(
                ['name' => $role->value],
                ['guard_name' => 'web']
            );
        }
    }
}