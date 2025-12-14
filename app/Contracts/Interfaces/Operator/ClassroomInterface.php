<?php

namespace App\Contracts\Interfaces\Operator;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;

interface ClassroomInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, SearchInterface, PaginateInterface
{
    public function graduateClass(string $classroomId): void;
    public function getWithSchedules(): Collection;
    public function getWithSchedulesById(string $id): Classroom;
}