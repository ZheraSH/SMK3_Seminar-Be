<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\GenderEnum;
use App\Enums\RoleEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); //Name
            $table->string('image')->nullable();
            $table->string('NIP', 18)->unique();
            $table->string('NIK', 16)->nullable();
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->enum('gender',[GenderEnum::MALE->value, GenderEnum::FEMALE->value])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->enum('employment_status',[RoleEnum::TEACHER->value, RoleEnum::STAFF->value]); // staffTu, guru-> guru pengajar(default), walikelas, bk, WakaKurikulum,
            $table->boolean('active')->default(true);
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
