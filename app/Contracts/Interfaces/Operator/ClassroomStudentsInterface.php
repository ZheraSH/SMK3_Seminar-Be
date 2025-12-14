<?php

namespace App\Contracts\Interfaces\Operator;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ClassroomStudentsInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface
{
    public function getByClassroom(string $classroomId, Request $request = null): mixed;
    public function getAvailableStudents(string $classroomId, ?string $search, int $limit = 10): Collection;
    public function addStudents(string $classroomId, array $studentIds): void;
    public function removeStudent(string $classroomId, string $studentId): void;
}   