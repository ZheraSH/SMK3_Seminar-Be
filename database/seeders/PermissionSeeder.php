<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission->value],
                [
                    'id' => Str::uuid(),
                    'guard_name' => 'web'
                ]
            );
        }
    }
}