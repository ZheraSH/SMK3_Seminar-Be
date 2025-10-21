<?php

namespace App\Providers;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Contracts\Interfaces\ReligionInterface;
use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Repositories\SchoolYearRepository;
use App\Contracts\Repositories\ReligionRepository;
use App\Contracts\Repositories\StudentRepository;
use App\Contracts\Repositories\UserRepository;
use App\Models\SchoolYear;
use App\Models\Student; 
use App\Models\Religion;
use App\Models\User;
use App\Observers\StudentObserver;
use App\Observers\ReligionObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $register = [
        UserInterface::class => UserRepository::class,
        ReligionInterface::class => ReligionRepository::class,
        StudentInterface::class => StudentRepository::class,
        SchoolYearInterface::class => SchoolYearRepository::class,
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
        Religion::observe(ReligionObserver::class);
        User::observe(UserObserver::class);
    }
}
