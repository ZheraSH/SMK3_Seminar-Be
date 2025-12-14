<?php
namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\LevelClassRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ClassroomService
{
    private ClassroomRepository $classroomRepository;
    private MajorRepository $majorRepository;
    private LevelClassRepository $levelClassRepository;

    public function __construct(ClassroomRepository $classroomRepository, MajorRepository $majorRepository, LevelClassRepository $levelClassRepository)
    {
        $this->classroomRepository = $classroomRepository;
        $this->majorRepository = $majorRepository;
        $this->levelClassRepository = $levelClassRepository;
    }

    public function store(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {
            $major = $this->majorRepository->show($data['major_id']);
            $levelClass = $this->levelClassRepository->show($data['level_class_id']);

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
            return $this->classroomRepository->update($id, [
                'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null
            ]);
        });
    }

    public function show(string $id): Classroom
    {
        return $this->classroomRepository->show($id);
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->classroomRepository->search($request);
    }

    public function delete(string $id): bool
    {
        return $this->classroomRepository->delete($id);
    }

    public function graduateClass(string $classroomId): void
    {
        $this->classroomRepository->graduateClass($classroomId);
    }

    public function getWithSchedules()
    {
        return $this->classroomRepository->getWithSchedules();
    }

    public function getWithSchedulesById(string $id)
    {
        return $this->classroomRepository->getWithSchedulesById($id);
    }
}