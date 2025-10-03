<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DayEnum;
use App\Enums\roleEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->enum('day', [DayEnum::MONDAY->value, 
                                                    DayEnum::TUESDAY->value, 
                                                    DayEnum::WEDNESDAY->value, 
                                                    DayEnum::THURSDAY->value, 
                                                    DayEnum::FRIDAY->value, 
                                                    DayEnum::SATURDAY->value, 
                                                    DayEnum::SUNDAY->value
                                                ]);
            $table->enum('role', [RoleEnum::STUDENT->value]);
            $table->time('checkin_start')->nullable();
            $table->time('checkin_end')->nullable();
            $table->time('checkout_start')->nullable();
            $table->time('checkout_end')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};
