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
        Schema::create('promotion_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('from_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('to_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignUuid('from_school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignUuid('to_school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->string('from_level_name');
            $table->string('to_level_name');
            $table->timestamp('promoted_at');
            $table->timestamps();

            $table->unique(['student_id', 'from_classroom_id', 'to_school_year_id'], 'promotion_logs_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_logs');
    }
};
