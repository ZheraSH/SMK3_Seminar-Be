<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_has_roles', function ($table) {
            DB::statement('ALTER TABLE model_has_roles MODIFY model_id CHAR(36) NOT NULL');
        });
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function ($table) {
            DB::statement('ALTER TABLE model_has_roles MODIFY model_id BIGINT UNSIGNED NOT NULL');
        });
    }
};
