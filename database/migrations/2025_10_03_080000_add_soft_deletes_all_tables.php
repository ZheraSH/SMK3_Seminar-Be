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
            'contacts',
            'employees',
            'employee_positions',
            'faqs',
            'guest_books',
            'lesson_hours',
            'lesson_schedules',
            'level_classes',
            'majors',
            'mapels',
            'model_has_rfid',
            'attendances',
            'attendance_journals',
            'news',
            'positions',
            'religions',
            'repairs',
            'rules',
            'schools',
            'school_years',
            'students',
            'student_repairs',
            'student_violations',
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
            'contacts',
            'employees',
            'employee_positions',
            'faqs',
            'guest_books',
            'lesson_hours',
            'lesson_schedules',
            'level_classes',
            'majors',
            'mapels',
            'model_has_rfid',
            'attendances',
            'attendance_journals',
            'news',
            'positions',
            'religions',
            'repairs',
            'rules',
            'schools',
            'school_years',
            'students',
            'student_repairs',
            'student_violations',
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


