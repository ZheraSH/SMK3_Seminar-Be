<?php

use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;
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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('classroom_student_id')->nullable()->constrained('classroom_students')->nullOnDelete();
            $table->foreignUuid('rfid_id')->nullable()->constrained('rfids')->nullOnDelete();
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignUuid('lesson_schedule_id')->nullable()->constrained('lesson_schedules')->nullOnDelete();
            $table->date('date');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->integer('lesson_order')->default(1);
            $table->enum('attendance_type', ['rfid', 'cross_check'])->default('rfid');
            $table->enum('status', AttendanceStatusEnum::values())->default(AttendanceStatusEnum::ALPHA->value);
            $table->enum('proof', AttendanceProofEnum::values())->default(AttendanceProofEnum::MANUAL->value);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['classroom_student_id', 'date', 'lesson_order']);
            $table->index(['teacher_id', 'date']);

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
        Schema::dropIfExists('attendances');
    }
};
