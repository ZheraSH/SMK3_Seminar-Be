<?php

namespace App\Providers;

use App\Contracts\Interfaces\ClassroomInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Interfaces\ClassroomTeachersInterface;
use App\Contracts\Interfaces\EmployeeInterface;
use App\Contracts\Interfaces\LevelClassInterface;
use App\Contracts\Interfaces\MajorInterface;
use App\Contracts\Interfaces\SchoolYearInterface;
use App\Contracts\Interfaces\ReligionInterface;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Repositories\ClassroomRepository;
use App\Contracts\Repositories\ClassroomStudentsRepository;
use App\Contracts\Repositories\ClassroomTeachersRepository;
use App\Contracts\Repositories\EmployeeRepository;
use App\Contracts\Repositories\LevelClassRepository;
use App\Contracts\Repositories\MajorRepository;
use App\Contracts\Repositories\ReligionRepository;
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
use App\Models\Classroom;
use App\Models\ClassroomStudents;
use App\Models\ClassroomTeachers;
use App\Models\LevelClass;
use App\Models\Major;
use App\Observers\ClassroomObserver;
use App\Observers\ClassroomStudentsObserver;
use App\Observers\ClassroomTeachersObserver;
use App\Observers\LevelClassObserver;
use App\Observers\MajorObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $register = [
        UserInterface::class => UserRepository::class,
        ReligionInterface::class => ReligionRepository::class,
        StudentInterface::class => StudentRepository::class,
        EmployeeInterface::class => EmployeeRepository::class,
        SchoolYearInterface::class => SchoolYearRepository::class,
        MajorInterface::class => MajorRepository::class,
        // LevelClassInterface::class => LevelClassRepository::class,
        // ClassroomInterface::class => ClassroomRepository::class,
        // ClassroomStudentsInterface::class => ClassroomStudentsRepository::class,
        // ClassroomTeachersInterface::class => ClassroomTeachersRepository::class,
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

        Major::observe(MajorObserver::class);
        // LevelClass::observe(LevelClassObserver::class);
        // Classroom::observe(ClassroomObserver::class);
        // ClassroomStudents::observe(ClassroomStudentsObserver::class);
        // ClassroomTeachers::observe(ClassroomTeachersObserver::class);
    }
}
