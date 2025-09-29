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
        Schema::create('model_has_rfid', function (Blueprint $table) {
            $table->id();
            $table->string('rfid')->unique();
            $table->enum('model_type',['Student','Employee','School']);
            $table->unsignedBigInteger('model_id'); // ID dari model sesuai tipe
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_rfid');
    }
};
