<?php
        
namespace App\Contracts\Interfaces;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use Illuminate\Http\Request;

interface ClassroomStudentsInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, SearchInterface, PaginateInterface
{
    public function getByStudentId(string $studentId): mixed;
    public function getByClassroom(string $classroomId, Request $request = null);
    public function getByClassroomForAttendance(string $classroomId, Request $request = null);
    public function getByStudentAndClassroom(string $studentId, string $classroomId): mixed;
    public function countActiveByClassroom(string $classroomId): int;
    public function getLatestByStudent(string $studentId);

}