<?php

namespace App\Providers;

use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Interfaces\Operator\RoleInterface;
use App\Contracts\Interfaces\Operator\SchoolInterface;
use App\Contracts\Interfaces\Operator\ReligionInterface;
use App\Contracts\Interfaces\Operator\EmployeeInterface;
use App\Contracts\Interfaces\Operator\StudentInterface;
use App\Contracts\Interfaces\Operator\LevelClassInterface;
use App\Contracts\Interfaces\Operator\MajorInterface;
use App\Contracts\Interfaces\Operator\SubjectInterface;
use App\Contracts\Interfaces\Operator\SchoolYearInterface;
use App\Contracts\Interfaces\Operator\ClassroomInterface;
use App\Contracts\Interfaces\Operator\ClassroomStudentsInterface;
use App\Contracts\Interfaces\Operator\LessonHourInterface;
use App\Contracts\Interfaces\Operator\LessonScheduleInterface;
use App\Contracts\Interfaces\Operator\AttendanceRuleInterface;
use App\Contracts\Interfaces\Operator\RfidInterface;
use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\AttendanceGlobalInterface;
use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Contracts\Interfaces\AttendanceMonitoringInterface;
use App\Contracts\Interfaces\StudentLessonScheduleInterface;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\Operator\RoleRepository;
use App\Contracts\Repositories\Operator\SchoolRepository;
use App\Contracts\Repositories\Operator\ReligionRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\LevelClassRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Contracts\Repositories\Operator\SchoolYearRepository;
use App\Contracts\Repositories\Operator\SubjectRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Contracts\Repositories\Operator\LessonHourRepository;
use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\AttendanceRuleRepository;
use App\Contracts\Repositories\Operator\RfidRepository;
use App\Contracts\Repositories\AttendanceRepository;
use App\Contracts\Repositories\AttendanceGlobalRepository;
use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Contracts\Repositories\AttendanceMonitoringRepository;
use App\Contracts\Repositories\StudentLessonScheduleRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $register = [
        UserInterface::class => UserRepository::class,
        RoleInterface::class => RoleRepository::class,
        SchoolInterface::class => SchoolRepository::class,
        ReligionInterface::class => ReligionRepository::class,
        StudentInterface::class => StudentRepository::class,
        EmployeeInterface::class => EmployeeRepository::class,
        SchoolYearInterface::class => SchoolYearRepository::class,
        MajorInterface::class => MajorRepository::class,
        LevelClassInterface::class => LevelClassRepository::class,
        ClassroomInterface::class => ClassroomRepository::class,
        ClassroomStudentsInterface::class => ClassroomStudentsRepository::class,
        SubjectInterface::class => SubjectRepository::class,
        LessonHourInterface::class => LessonHourRepository::class,
        LessonScheduleInterface::class => LessonScheduleRepository::class,
        AttendanceRuleInterface::class => AttendanceRuleRepository::class,
        RfidInterface::class => RfidRepository::class,
        AttendanceInterface::class => AttendanceRepository::class,
        AttendancePermissionInterface::class => AttendancePermissionRepository::class,
        StudentLessonScheduleInterface::class => StudentLessonScheduleRepository::class,
        AttendanceMonitoringInterface::class => AttendanceMonitoringRepository::class,
        AttendanceGlobalInterface::class => AttendanceGlobalRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->register as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }

    public function boot(): void
    {
        config(['app.locale' => 'id']);
        \Carbon\Carbon::setLocale('id');
    }
}
