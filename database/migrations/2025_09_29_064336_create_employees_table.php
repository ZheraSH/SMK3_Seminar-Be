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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('NIP')->unique();
            $table->string('NIK')->nullable();
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('employment_status')->nullable(); // staffTu, guru-> guru pengajar(default), walikelas, bk, WakaKurikulum,
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
