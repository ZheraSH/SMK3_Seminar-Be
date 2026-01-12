<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_final')->default(false);
            $table->uuid('overridden_by_permission_id')->nullable();

            $table->foreign('overridden_by_permission_id')->references('id')->on('attendance_permissions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['overridden_by_permission_id']);
            $table->dropColumn(['is_locked', 'is_final', 'overridden_by_permission_id']);
        });
    }
};
