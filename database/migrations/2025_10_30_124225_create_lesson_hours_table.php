<?php

use App\Enums\DayEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lesson_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('day', [DayEnum::MONDAY->value, DayEnum::TUESDAY->value, DayEnum::WEDNESDAY->value, DayEnum::THURSDAY->value, DayEnum::FRIDAY->value, DayEnum::SATURDAY->value, DayEnum::SUNDAY->value]);
            $table->string('name');
            $table->time('start');
            $table->time('end');
            $table->boolean('is_lesson')->default(true)->comment('true for lesson periods, false for breaks');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('lesson_hours');
    }
};