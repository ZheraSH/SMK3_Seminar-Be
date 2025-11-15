<?php
namespace App\Services;

use App\Contracts\Interfaces\ClassroomInterface;
use App\Contracts\Interfaces\LevelClassInterface;
use App\Contracts\Interfaces\MajorInterface;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ClassroomService
{
    private ClassroomInterface $classroomInterface;
    private MajorInterface $majorInterface;
    private LevelClassInterface $levelClassInterface;

    public function __construct(
        ClassroomInterface $classroomInterface,
        MajorInterface $majorInterface,
        LevelClassInterface $levelClassInterface
    ) {
        $this->classroomInterface = $classroomInterface;
        $this->majorInterface = $majorInterface;
        $this->levelClassInterface = $levelClassInterface;
    }

    public function store(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            $major = $this->majorInterface->show($data['major_id']);
            $levelClass = $this->levelClassInterface->show($data['level_class_id']);

            $number = trim($data['name']); 
            $data['name'] = sprintf('%s %s %s', strtoupper($levelClass->name), strtoupper($major->code), $number);
            $data['slug'] = strtoupper(sprintf('%s-%s-%s', $levelClass->name, $major->code, $number));
            $data['id'] = (string) Str::uuid();

            return $this->classroomInterface->store($data);
        });
    }

    public function update(string $id, array $data): Classroom
    {
        return DB::transaction(function () use ($id, $data) {
            $this->classroomInterface->update($id, [
                'teacher_id' => $data['teacher_id']
            ]);
            
            return $this->classroomInterface->show($id);
        });
    }

    public function show(string $id): Classroom
    {
        return $this->classroomInterface->show($id);
    }

    public function paginate(): mixed
    {
        return $this->classroomInterface->paginate();
    }

    public function delete(string $id): bool
    {
        return $this->classroomInterface->delete($id);
    }

    public function addStudents(Classroom $classroom, array $studentIds): Classroom
    {
        return $this->classroomInterface->addStudentsToClassroom($classroom->id, $studentIds);
    }

    public function removeStudent(Classroom $classroom, string $studentId): Classroom
    {
        return $this->classroomInterface->removeStudentFromClassroom($classroom->id, $studentId);
    }

    public function syncStudents(Classroom $classroom, array $studentIds): Classroom
    {
        return $this->classroomInterface->syncClassroomStudents($classroom->id, $studentIds);
    }

    public function getActiveStudents(Classroom $classroom): Collection
    {
        return $this->classroomInterface->getActiveStudents($classroom->id);
    }

    public function search(Request $request)
    {
        return $this->classroomInterface->search($request);
    }

    public function getAvailableStudents(Classroom $classroom, string $search = null, int $limit = 10): Collection
    {
        return $this->classroomInterface->getAvailableStudents($classroom->id, $search, $limit);
    }

    public function graduateClass(string $classroomId): void
    {
        $this->classroomInterface->graduateClass($classroomId);
    }

    public function getWithSchedules()
    {
        return $this->classroomInterface->getWithSchedules();
    }

    public function getWithSchedulesById(string $id)
    {
        return $this->classroomInterface->getWithSchedulesById($id);
    }
}