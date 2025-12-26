<?php

use App\Enums\PermissionStatusEnum;
use App\Enums\PermissionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', PermissionTypeEnum::values());
            $table->enum('status', PermissionStatusEnum::values())->default(PermissionStatusEnum::PENDING->value);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('proof')->nullable();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('counselor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('type');
            $table->index(['start_date', 'end_date']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_permissions');
    }
};
