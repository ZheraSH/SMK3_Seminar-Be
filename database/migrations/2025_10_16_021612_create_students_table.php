<?php

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
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
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('nisn', 17)->unique();
            $table->foreignUuid('religion_id')->constrained('religions')->OnDelete('cascade');
            $table->enum('gender', GenderEnum::values());
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('address');
            $table->string('number_kk');
            $table->string('number_akta');
            $table->integer('order_child')->nullable();
            $table->integer('count_siblings')->nullable();
            $table->enum('status', StudentStatusEnum::values()) ->default(StudentStatusEnum::ACTIVE->value);
            $table->integer('point')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
