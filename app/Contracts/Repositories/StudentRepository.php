<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\StudentInterface;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository implements StudentInterface
{
    public function __construct(Student $student)
    {
        $this->model = $student;
    }

    /**
     * Get all students (with optional search).
     */
    public function getAll(Request $request): LengthAwarePaginator|Collection
    {
        return $this->model
            ->with(['user', 'religion'])
            ->when($request->search, fn($q) =>
                $q->whereHas('user', fn($user) =>
                    $user->where('name', 'LIKE', "%{$request->search}%")
                )
            )
            ->latest()
            ->paginate(10);
    }

    /**
     * Find student by UUID.
     */
    public function findByUuid(string $uuid): Student
    {
        return $this->model->where('id', $uuid)->firstOrFail();
    }

    /**
     * Store a new student record.
     */
    public function store(array $data): Student
    {
        return $this->model->create($data);
    }

    /**
     * Update student by UUID.
     */
    public function update(string $uuid, array $data): Student
    {
        $student = $this->findByUuid($uuid);
        $student->update($data);
        return $student;
    }

    /**
     * Delete student by UUID.
     */
    public function delete(string $uuid): bool
    {
        $student = $this->findByUuid($uuid);
        return $student->delete();
    }
}
