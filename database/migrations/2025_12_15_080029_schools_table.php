<?php

use App\Enums\AccreditationEnum;
use App\Enums\SchoolTypeEnum;
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
        Schema::create('schools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('logo')->nullable();
            $table->string('name');
            $table->string('principal_name');
            $table->string('npsn')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('school_type', SchoolTypeEnum::values())->nullable();
            $table->enum('accreditation', AccreditationEnum::values())->nullable();
            $table->string('address')->nullable();
            $table->softDeletes();
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