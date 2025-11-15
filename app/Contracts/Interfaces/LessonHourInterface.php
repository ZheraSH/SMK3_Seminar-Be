<?php
namespace App\Contracts\Interfaces;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;

interface LessonHourInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface
{
    public function getByDay(string $day): mixed;
    public function checkNameExists(string $name, string $day, ?string $excludeId = null): bool;
    public function checkTimeOverlap(string $day, string $start, string $end, ?string $excludeId = null): bool;
    public function isUsedInSchedules(string $lessonHourId): bool;
}