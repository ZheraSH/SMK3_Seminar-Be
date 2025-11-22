<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AttendanceStatusEnum;
use App\Enums\AttendanceProofEnum;

return new class extends Migration
{
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
            $table->enum('status', [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::ALPHA->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::SICK->value
            ])->default(AttendanceStatusEnum::ALPHA->value);
            $table->enum('proof', [
                AttendanceProofEnum::RFID->value,
                AttendanceProofEnum::MANUAL->value,
                AttendanceProofEnum::CLASSROOM->value
            ])->default(AttendanceProofEnum::MANUAL->value);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['classroom_student_id', 'date', 'lesson_order']);
            $table->index(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};