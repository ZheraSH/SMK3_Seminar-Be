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
            //System
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            //Data Master
            SchoolSeeder::class,
            ReligionSeeder::class,
            MajorSeeder::class,
            LevelClassSeeder::class,
            SubjectSeeder::class,
            //Data Dummy
            EmployeeSeeder::class, //Data dummy
            StudentSeeder::class, //Data dummy
            SchoolYearSeeder::class, //Data dummy
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