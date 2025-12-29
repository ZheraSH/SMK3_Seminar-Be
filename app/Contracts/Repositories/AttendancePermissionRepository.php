<?php

    namespace App\Contracts\Repositories;

    use App\Contracts\Interfaces\AttendancePermissionInterface;
    use App\Enums\PermissionStatusEnum;
    use App\Models\AttendancePermission;
    use App\Traits\PaginationTrait;
    use Illuminate\Http\Request;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Database\Eloquent\Collection;

    class AttendancePermissionRepository extends BaseRepository implements AttendancePermissionInterface
    {
        use PaginationTrait;

        public function __construct(AttendancePermission $attendancePermission)
        {
            $this->model = $attendancePermission;
        }

        public function get(): Collection
        {
            return $this->model
                ->with(['student.user', 'counselor.user'])
                ->latest()
                ->get();
        }

        public function store(array $data): AttendancePermission
        {
            return $this->model->create($data);
        }

        public function show(mixed $id): AttendancePermission
        {
            return $this->model
                ->with(['student.user', 'counselor.user'])
                ->findOrFail($id);
        }

        public function update(mixed $id, array $data): bool
        {
            $model = $this->show($id);
            return $model->update($data);
        }

        public function delete(mixed $id): bool
        {
            return $this->show($id)->delete();
        }

        public function paginate(): LengthAwarePaginator
        {
            return $this->model
                ->with(['student.user', 'counselor.user'])
                ->latest()
                ->paginate(8);
        }

        public function findByStudent(string $studentId, Request $request = null): LengthAwarePaginator
        {
            $query = $this->model
                ->with(['student.user', 'counselor.user'])
                ->where('student_id', $studentId)
                ->latest();

            return $query->paginate(8);
        }

        public function deleteIfPending(string $id, string $studentId): bool
        {
            $permission = $this->model
                ->where('id', $id)
                ->where('student_id', $studentId)
                ->where('status', PermissionStatusEnum::PENDING)
                ->firstOrFail();

            return $permission->delete();
        }

        public function getPendingPermissions(): Collection
        {
            return $this->model
                ->with(['student.user', 'counselor.user'])
                ->where('status', PermissionStatusEnum::PENDING)
                ->latest()
                ->get();
        }

        public function approvePermission(string $id, string $counselorId): AttendancePermission
        {
            $permission = $this->show($id);

            $permission->update([
                'status' => PermissionStatusEnum::APPROVED->value,
                'counselor_id' => $counselorId,
                'verified_at' => now(),
            ]);

            return $this->show($id);
        }

        public function rejectPermission(string $id, string $counselorId): AttendancePermission
        {
            $permission = $this->show($id);

            $permission->update([
                'status' => PermissionStatusEnum::REJECTED->value,
                'counselor_id' => $counselorId,
                'verified_at' => now(),
            ]);

            return $this->show($id);
        }

        public function searchByCounselor(Request $request): LengthAwarePaginator
        {
            $query = $this->model
                ->with(['student.user', 'counselor.user'])
                ->when($request->search, function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->whereHas('student.user', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->search . '%');
                        })
                        ->orWhereHas('student', function ($sub) use ($request) {
                            $sub->where('nisn', 'LIKE', '%' . $request->search . '%');
                        });
                    });
                })
                ->when($request->filter, function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->whereHas('student.classroomStudents.classroom', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->filter . '%');
                        })
                        ->orWhereHas('student.classroomStudents.classroom.major', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->filter . '%');
                        })
                        ->orWhereHas('student.classroomStudents.classroom.levelClass', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->filter . '%');
                        });
                    });
                })
                ->latest();

            return $query->paginate(8);
        }

        public function count(): int
        {
            return $this->model->query()->count();
        }

         public function getLatest(string $studentId): Collection
        {
        $permissions = $this->model
            ->where('student_id', $studentId)
            ->with(['student.user', 'counselor.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        \Log::info("Latest permissions for student {$studentId}: {$permissions->count()}");

        return $permissions;
        }

}
