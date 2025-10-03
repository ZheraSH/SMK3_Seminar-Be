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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('classroom_student_id')->constrained('student_classrooms')->onDelete('cascade');
            $table->integer('point')->default(10);
            $table->enum('status',['alpha','sick','permission'])->nullable();
            $table->string('proof')->nullable();
            $table->time('checkin')->nullable();
            $table->time('checkout')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
