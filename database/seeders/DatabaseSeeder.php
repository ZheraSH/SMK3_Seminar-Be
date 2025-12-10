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
            UserSeeder::class,
            ReligionSeeder::class,
            StudentSeeder::class, //Data dummy
            EmployeeSeeder::class, //Data dummy
            MajorSeeder::class,
            LevelClassSeeder::class,
            SchoolYearSeeder::class, //Data dummy
            SubjectSeeder::class,
            ClassroomSeeder::class, //Data dummy
            ClassroomStudentSeeder::class, //Data dummy
            LessonHourSeeder::class, //Data dummy
            LessonScheduleSeeder::class, //Data dummy
            AttendanceRuleSeeder::class, //Data dummy
            RfidSeeder::class, //Data Dummy
            AttendanceSeeder::class, //Data dummy
            AttendancePermissionSeeder::class, //Data dummy
        ]);
    }
}
