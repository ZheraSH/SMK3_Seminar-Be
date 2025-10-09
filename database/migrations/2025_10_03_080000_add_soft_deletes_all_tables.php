<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'classrooms',
            'classroom_students',
            'employees',
            'lesson_hours',
            'lesson_schedules',
            'level_classes',
            'majors',
            'mapels',
            'model_has_rfid',
            'attendances',
            'attendance_journals',
            'religions',
            'schools',
            'school_years',
            'students',
            'teacher_journals',
            'teacher_mapels',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'classrooms',
            'classroom_students',
            'employees',
            'lesson_hours',
            'lesson_schedules',
            'level_classes',
            'majors',
            'mapels',
            'model_has_rfid',
            'attendances',
            'attendance_journals',
            'religions',
            'schools',
            'school_years',
            'students',
            'teacher_journals',
            'teacher_mapels',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->dropSoftDeletes();
                });
            }
        }
    }
};


