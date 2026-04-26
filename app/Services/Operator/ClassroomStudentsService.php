<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Imports\ClassroomStudentImport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ClassroomStudentsService
{
    private ClassroomStudentsRepository $classroomStudentsRepository;

    public function __construct(ClassroomStudentsRepository $classroomStudentsRepository)
    {
        $this->classroomStudentsRepository = $classroomStudentsRepository;
    }

    public function getByClassroom(string $classroomId, Request $request)
    {
        return $this->classroomStudentsRepository->getByClassroom($classroomId, $request);
    }

    public function getAvailableStudents(string $classroomId, Request $request)
    {
        return $this->classroomStudentsRepository->getAvailableStudents(
            $classroomId,
            $request->search,
            $request->limit ?? 10
        );
    }

    public function addStudents(string $classroomId, array $studentIds): Collection
    {
        $this->classroomStudentsRepository->addStudents($classroomId, $studentIds);

        return $this->classroomStudentsRepository->getByClassroom($classroomId, new Request(['limit' => 1000]))
            ->getCollection();
    }

    public function removeStudent(string $classroomId, string $studentId): Collection
    {
        $this->classroomStudentsRepository->removeStudent($classroomId, $studentId);

        return $this->classroomStudentsRepository->getByClassroom($classroomId, new Request(['limit' => 1000]))
            ->getCollection();
    }

    public function promoteClass(string $currentClassroomId, string $newClassroomId): void
    {
        $this->classroomStudentsRepository->promoteClass($currentClassroomId, $newClassroomId);
    }

    public function importStudents(string $classroomId, mixed $file): array
    {
        $import = new ClassroomStudentImport($classroomId);
        Excel::import($import, $file);

        return [
            'imported_count' => $import->importedCount,
            'errors'         => $import->errors,
        ];
    }
}
