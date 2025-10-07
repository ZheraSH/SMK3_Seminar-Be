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
        Schema::create('attendance_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_journal_id')->constrained('teacher_journals')->onDelete('cascade');
            $table->foreignId('classroom_student_id')->constrained('classroom_students')->onDelete('cascade');
            $table->foreignId('lesson_hour_id')->constrained('lesson_hours')->onDelete('cascade');
            $table->enum('status',['alpha','sick','permission'])->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_journals');
    }
};
