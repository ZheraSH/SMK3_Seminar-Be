<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $reflection = new \ReflectionClass(RoleEnum::class);

        foreach ($reflection->getConstants() as $case) {
            Role::create([
                'name' => $case
            ]);
        }
    }
}

//Done