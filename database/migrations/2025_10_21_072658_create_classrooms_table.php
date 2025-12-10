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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->foreignUuid('major_id')->constrained('majors')->onDelete('restrict');
            $table->string('slug')->unique();
            $table->foreignUuid('level_class_id')->constrained('level_classes')->onDelete('restrict');
            $table->foreignUuid('school_year_id')->constrained('school_years')->onDelete('restrict');
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
