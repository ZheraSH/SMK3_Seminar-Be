<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $reflection = new \ReflectionClass(PermissionEnum::class);

        foreach ($reflection->getConstants() as $case) {
            Permission::create([
                'name' => $case
            ]);
        }
    }
}
