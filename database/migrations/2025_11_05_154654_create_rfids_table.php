<?php

use App\Enums\RfidStatusEnum;
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
        Schema::create('rfids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rfid')->unique();
            $table->foreignUuid('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->enum('status', RfidStatusEnum::values())->default(RfidStatusEnum::INACTIVE->value);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfids');
    }
};
