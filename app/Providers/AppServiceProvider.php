<?php

namespace App\Providers;

use App\Contracts\Interfaces\EmployeeInterface;
use App\Contracts\Interfaces\ReligionInterface;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Repositories\EmployeeRepository;
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
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $register = [
        UserInterface::class => UserRepository::class,
        ReligionInterface::class => ReligionRepository::class,
        StudentInterface::class => StudentRepository::class,
        EmployeeInterface::class => EmployeeRepository::class,
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

        Student::observe(StudentObserver::class);
        Employee::observe(EmployeeObserver::class);
        Religion::observe(ReligionObserver::class);
        User::observe(UserObserver::class);
    }
}
