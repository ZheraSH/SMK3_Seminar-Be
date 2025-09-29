<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Unique;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('NPSN')->unique();
            $table->string('phone_number')->nullable();
            $table->string('image')->nullable();
            $table->string('pos_code')->nullable();
            $table->string('address')->nullable();
            $table->string('head_school')->nullable();
            $table->string('NIP')->nullable(); //Kepala Sekolah
            $table->string('website_school')->nullable();
            $table->enum('accreditation',['A','B','C'])->nullable();
            $table->integer('max_point')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
