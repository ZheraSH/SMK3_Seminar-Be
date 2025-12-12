<?php

namespace App\Contracts\Interfaces\Operator;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use App\Contracts\Interfaces\Eloquent\GetActiveInterface;
use App\Contracts\Interfaces\Eloquent\CountInterface;
use Illuminate\Database\Eloquent\Collection;

interface StudentInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, PaginateInterface, SearchInterface, GetActiveInterface, CountInterface
{
    public function showWithActiveClassroom(mixed $id): mixed;
    public function getWithActiveClassrooms(): Collection;
    public function getClassroomInfo(string $studentId): mixed;
    public function findWithClassroom(string $id): mixed;
}