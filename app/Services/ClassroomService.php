<?php

namespace App\Services;

use App\Contracts\Interfaces\ClassroomInterface;
use App\Models\Classroom;
use App\Models\Major;
use App\Models\LevelClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ClassroomService
{
    private ClassroomInterface $classroomRepository;

    public function __construct(ClassroomInterface $classroomRepository)
    {
        $this->classroomRepository = $classroomRepository;
    }

    public function store(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            $major = Major::findOrFail($data['major_id']);
            $levelClass = LevelClass::findOrFail($data['level_class_id']);

            $number = trim($data['name']); 
            $data['name'] = sprintf('%s %s %s', strtoupper($levelClass->name), strtoupper($major->code), $number);
            $data['slug'] = strtoupper(sprintf('%s-%s-%s', $levelClass->name, $major->code, $number));
            $data['id'] = (string) Str::uuid();

            return $this->classroomRepository->store($data);
        });
    }

    public function update(string $id, array $data): Classroom
    {
        return DB::transaction(function () use ($id, $data) {
            $this->classroomRepository->update($id, [
                'teacher_id' => $data['teacher_id']
            ]);
            
            return $this->classroomRepository->show($id);
        });
    }

    public function delete(string $id): bool
    {
        return $this->classroomRepository->delete($id);
    }

    public function addStudents(Classroom $classroom, array $studentIds): Classroom
    {
        return $this->classroomRepository->addStudentsToClassroom($classroom->id, $studentIds);
    }

    public function removeStudent(Classroom $classroom, string $studentId): Classroom
    {
        return $this->classroomRepository->removeStudentFromClassroom($classroom->id, $studentId);
    }

    public function syncStudents(Classroom $classroom, array $studentIds): Classroom
    {
        return $this->classroomRepository->syncClassroomStudents($classroom->id, $studentIds);
    }

    public function getActiveStudents(Classroom $classroom): Collection
    {
        return $this->classroomRepository->getActiveStudents($classroom->id);
    }

    public function search(Request $request)
    {
        return $this->classroomRepository->search($request);
    }

    public function getAvailableStudents(Classroom $classroom, string $search = null, int $limit = 10): Collection
    {
        return $this->classroomRepository->getAvailableStudents($classroom->id, $search, $limit);
    }

    public function graduateClass(string $classroomId): void
    {
        $this->classroomRepository->graduateClass($classroomId);
    }
}
