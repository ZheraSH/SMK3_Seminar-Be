<?php

use App\Enums\AttendanceStatusEnum;
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
        Schema::create('attendance_rfids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignUuid('classroom_student_id')->nullable()->constrained('classroom_students')->nullOnDelete();
            $table->foreignUuid('rfid_id')->nullable()->constrained('rfids')->nullOnDelete();
            $table->date('date');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->enum('status', [AttendanceStatusEnum::PRESENT->value, AttendanceStatusEnum::LATE->value])->default(AttendanceStatusEnum::PRESENT->value);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rfids');
    }
};
