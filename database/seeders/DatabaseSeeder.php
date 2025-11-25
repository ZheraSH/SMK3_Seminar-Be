<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class, //Data dummy
            ReligionSeeder::class,
            StudentSeeder::class, //Data dummy
            EmployeeSeeder::class, //Data dummy
            SchoolYearSeeder::class,
            MajorSeeder::class,
            LevelClassSeeder::class,
            SubjectSeeder::class,
            ClassroomSeeder::class, //Data dummy
            ClassroomStudentSeeder::class, //Data dummy
            LessonHourSeeder::class, //Data dummy
            LessonScheduleSeeder::class, //Data dummy
            AttendanceRuleSeeder::class, //Data dummy
            RfidSeeder::class, //Data Dummy
            AttendancePermissionSeeder::class, //Data dummy
            AttendanceSeeder::class, //Data dummy
        ]);
    }
}
