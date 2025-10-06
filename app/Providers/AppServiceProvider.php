<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\StudentServiceInterface;
use App\Services\StudentService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\AuthService;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Services\AttendanceService;
use App\Services\Contracts\RfidAttendanceServiceInterface;
use App\Services\RfidAttendanceService;
use App\Services\Contracts\LessonAttendanceServiceInterface;
use App\Services\LessonAttendanceService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(RfidAttendanceServiceInterface::class, RfidAttendanceService::class);
        $this->app->bind(LessonAttendanceServiceInterface::class, LessonAttendanceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
