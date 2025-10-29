<?php

use App\Enums\ClassroomStudentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ClassroomStudentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('status', ClassroomStudentStatusEnum::values())->default(ClassroomStudentStatusEnum::ACTIVE->value);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_students');
    }
};