<?php
        
namespace App\Contracts\Interfaces;
        
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
    public function addStudentsToClassroom(string $classroomId, array $studentIds): Classroom;
    public function removeStudentFromClassroom(string $classroomId, string $studentId): Classroom;
    public function syncClassroomStudents(string $classroomId, array $studentIds): Classroom;
    public function getActiveStudents(string $classroomId): Collection;
    public function getAvailableStudents(string $classroomId, string $search = null, int $limit = 10): Collection;
    public function graduateClass(string $classroomId): void;
}