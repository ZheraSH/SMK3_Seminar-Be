<?php

use App\Enums\GenderEnum;
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
            $table->Uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('NIP', 18)->unique();
            $table->string('NIK', 16);
            $table->foreignUuid('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->enum('gender',[GenderEnum::MALE->value, GenderEnum::FEMALE->value]);
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('address');
            $table->string('phone_number');
            $table->boolean('active')->default(true);
            $table->softDeletes();
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
