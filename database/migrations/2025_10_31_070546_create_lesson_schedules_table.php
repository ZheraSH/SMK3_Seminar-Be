<?php

use App\Enums\DayEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('day', [DayEnum::MONDAY->value, DayEnum::TUESDAY->value, DayEnum::WEDNESDAY->value, DayEnum::THURSDAY->value, DayEnum::FRIDAY->value, DayEnum::SATURDAY->value, DayEnum::SUNDAY->value]);
            $table->foreignUuid('subject_id')->constrained('subjects');
            $table->foreignUuid('employee_id')->constrained('employees');
            $table->foreignUuid('classroom_id')->constrained('classrooms');
            $table->foreignUuid('lesson_hour_id')->constrained('lesson_hours');
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }
};