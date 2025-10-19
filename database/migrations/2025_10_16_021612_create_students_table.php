<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\GenderEnum;

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
            $table->foreignUuid('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->enum('gender',[GenderEnum::MALE->value, GenderEnum::FEMALE->value])->nullable();
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('address');
            $table->string('number_kk');
            $table->string('number_akta');
            $table->integer('order_child')->nullable();
            $table->integer('count_siblings')->nullable();
            // $table->integer('point')->nullable();
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
