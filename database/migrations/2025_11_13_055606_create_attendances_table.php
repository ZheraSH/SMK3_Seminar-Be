<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AttendanceStatusEnum;
use App\Enums\TapTypeEnum;
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
            $table->date('date');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            
            $table->enum('status', [
                AttendanceStatusEnum::ON_TIME->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::ABSENT->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::SICK->value
            ])->default(AttendanceStatusEnum::ABSENT->value);
            $table->enum('tap_type', [
                TapTypeEnum::CHECKIN->value,
                TapTypeEnum::CHECKOUT->value,
                TapTypeEnum::CLASS_CHECKIN->value
            ])->nullable();
            $table->enum('proof', [
                AttendanceProofEnum::RFID->value,
                AttendanceProofEnum::MANUAL->value,
                AttendanceProofEnum::CLASSROOM->value,
                AttendanceProofEnum::ONLINE->value
            ])->default(AttendanceProofEnum::MANUAL->value);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index('classroom_student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};