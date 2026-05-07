<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\ClassroomInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Models\Classroom;
use App\Enums\StudentStatusEnum;
use App\Traits\PaginationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ClassroomRepository extends BaseRepository implements ClassroomInterface
{
    use PaginationTrait;

    public function __construct(Classroom $classroom)
    {
        $this->model = $classroom;
    }

    protected function baseQuery()
    {
        return $this->model->query()
            ->where(function ($q) {
                $q->whereHas('schoolYear', fn($s) => $s->where('active', true))
                  ->orWhereHas('classroomStudents', fn($cs) => $cs->where('status', StudentStatusEnum::ACTIVE->value));
            })
            ->with([
                'major:id,name,code',
                'levelClass:id,name',
                'schoolYear:id,name',
                'homeroomTeacher.user:id,name',
                'classroomStudents' => function ($q) {
                    $q->where('status', StudentStatusEnum::ACTIVE->value)
                        ->with('student.user:id,name');
                }
            ]);
    }

    public function get(): Collection
    {
        return $this->baseQuery()->latest()->get();
    }

    public function store(array $data): Classroom
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): Classroom
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->baseQuery()->latest()->paginate(9);
    }

    public function search(Request $request, int $pagination = 9): mixed
    {
        return $this->baseQuery()
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhereHas(
                            'major',
                            fn($m) =>
                            $m->where('name', 'like', "%{$request->search}%")
                                ->orWhere('code', 'like', "%{$request->search}%")
                        )
                        ->orWhereHas(
                            'levelClass',
                            fn($l) =>
                            $l->where('name', 'like', "%{$request->search}%")
                        )
                        ->orWhereHas(
                            'homeroomTeacher.user',
                            fn($t) =>
                            $t->where('name', 'like', "%{$request->search}%")
                        );
                });
            })
            ->when(
                $request->major,
                fn($q) =>
                $q->whereHas('major', fn($m) => $m->where('code', $request->major))
            )
            ->when(
                $request->level_class,
                fn($q) =>
                $q->whereHas('levelClass', fn($l) => $l->where('name', $request->level_class))
            )
            ->when(
                $request->school_year,
                fn($q) =>
                $q->whereHas('schoolYear', fn($s) => $s->where('name', $request->school_year))
            )
            ->latest()
            ->paginate($pagination);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function countActive(): int
    {
        return $this->model->whereHas('schoolYear', function ($query) {
            $query->where('active', true);
        })->count();
    }

    public function graduateClass(string $classroomId): void
    {
        $classroom = $this->model->findOrFail($classroomId);

        if ($classroom->levelClass->name === 'XII') {
            $classroom->classroomStudents()
                ->update(['status' => StudentStatusEnum::GRADUATED->value]);
        }
    }

    public function getWithSchedules(): Collection
    {
        return $this->baseQuery()
            ->with([
                'lessonSchedules.lessonHour',
                'lessonSchedules.subject',
                'lessonSchedules.teacher.user',
            ])
            ->get();
    }

    public function getWithSchedulesById(string $id): Classroom
    {
        return $this->baseQuery()
            ->with([
                'lessonSchedules.lessonHour',
                'lessonSchedules.subject',
                'lessonSchedules.teacher.user',
            ])
            ->findOrFail($id);
    }
}
