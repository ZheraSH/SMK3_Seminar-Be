<?php

use App\Enums\DayEnum;
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
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('day', DayEnum::values());
            $table->foreignUuid('classroom_id')->constrained('classrooms');
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects');
            $table->foreignUuid('teacher_id')->nullable()->constrained('employees');
            $table->foreignUuid('lesson_hour_id')->constrained('lesson_hours');
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }
};