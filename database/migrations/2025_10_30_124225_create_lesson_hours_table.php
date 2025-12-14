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
            $table->enum('day', DayEnum::values());
            $table->string('name');
            $table->time('start');
            $table->time('end');
            $table->boolean('is_lesson')->default(true);
            $table->unsignedInteger('order');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['day', 'is_lesson', 'order']);
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