<?php

namespace App\Providers;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Contracts\Interfaces\AttendanceRuleInterface;
use App\Contracts\Interfaces\SubjectInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Interfaces\EmployeeInterface;
use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\LevelClassInterface;
use App\Contracts\Interfaces\MajorInterface;
use App\Contracts\Interfaces\SchoolYearInterface;
use App\Contracts\Interfaces\ReligionInterface;
use App\Contracts\Interfaces\RfidInterface;
use App\Contracts\Interfaces\RoleInterface;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Repositories\AttendanceRuleRepository;
use App\Contracts\Repositories\ClassroomRepository;
use App\Contracts\Repositories\ClassroomStudentsRepository;
use App\Contracts\Repositories\EmployeeRepository;
use App\Contracts\Repositories\LessonScheduleRepository;
use App\Contracts\Repositories\LevelClassRepository;
use App\Contracts\Repositories\MajorRepository;
use App\Contracts\Repositories\ReligionRepository;
use App\Contracts\Repositories\RfidRepository;
use App\Contracts\Repositories\RoleRepository;
use App\Contracts\Repositories\StudentRepository;
use App\Contracts\Repositories\UserRepository;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Religion;
use App\Models\User;
use App\Observers\StudentObserver;
use App\Observers\EmployeeObserver;
use App\Observers\ReligionObserver;
use App\Observers\UserObserver;
use App\Contracts\Repositories\SchoolYearRepository;
use App\Contracts\Repositories\SubjectRepository;
use App\Contracts\Repositories\LessonHourRepository;
use App\Models\AttendanceRule;
use App\Models\Classroom;
use App\Models\ClassroomStudents;
use App\Models\LessonSchedule;
use App\Models\LevelClass;
use App\Models\Major;
use App\Models\Rfid;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\LessonHour;
use App\Observers\AttendanceRuleObserver;
use App\Observers\ClassroomObserver;
use App\Observers\ClassroomStudentsObserver;
use App\Observers\LessonScheduleObserver;
use App\Observers\LevelClassObserver;
use App\Observers\MajorObserver;
use App\Observers\RfidObserver;
use App\Observers\SchoolYearObserver;
use App\Observers\SubjectObserver;
use App\Observers\LessonHourObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $register = [
        UserInterface::class => UserRepository::class,
        RoleInterface::class => RoleRepository::class,
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
        // AttendanceRuleInterface::class => AttendanceRuleRepository::class,
        // RfidInterface::class => RfidRepository::class,
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

        User::observe(UserObserver::class);
        Religion::observe(ReligionObserver::class);
        Student::observe(StudentObserver::class);
        Employee::observe(EmployeeObserver::class);
        SchoolYear::observe(SchoolYearObserver::class);
        Major::observe(MajorObserver::class);
        LevelClass::observe(LevelClassObserver::class);
        Classroom::observe(ClassroomObserver::class);
        ClassroomStudents::observe(ClassroomStudentsObserver::class);
        Subject::observe(SubjectObserver::class);
        LessonHour::observe(LessonHourObserver::class);
        LessonSchedule::observe(LessonScheduleObserver::class);
        // AttendanceRule::observe(AttendanceRuleObserver::class);
        // Rfid::observe(RfidObserver::class);
    }
}
