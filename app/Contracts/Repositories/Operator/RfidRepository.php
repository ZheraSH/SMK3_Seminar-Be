<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\RfidInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Enums\RfidStatusEnum;
use App\Enums\StudentStatusEnum;
use App\Models\Rfid;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RfidRepository extends BaseRepository implements RfidInterface
{
    public function __construct(Rfid $rfid)
    {
        $this->model = $rfid;
    }

    public function get(): Collection
    {
        return $this->model->query()
            ->with(['student.user'])
            ->latest()
            ->get();
    }

    public function store(array $data): Rfid
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): Rfid
    {
        return $this->model->query()
            ->with(['student.user'])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    public function unassignStudent(mixed $id): Rfid
    {
        $rfid = $this->show($id);
        $rfid->update([
            'student_id' => null,
            'status'     => RfidStatusEnum::INACTIVE->value,
        ]);
        return $rfid->fresh();
    }

    public function assignStudent(mixed $id, string $studentId): Rfid
    {
        $rfid = $this->show($id);
        $rfid->update([
            'student_id' => $studentId,
            'status'     => RfidStatusEnum::ACTIVE->value,
        ]);
        return $rfid->fresh(['student.user']);
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->model->query()->with(['student.user'])->latest()->paginate(10);
    }

    public function search(Request $request, int $pagination = 10): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['student.user'])
            ->when($request->search, function ($query) use ($request) {
                $query->where('rfid', 'LIKE', '%' . $request->search . '%')
                    ->orWhereHas('student.user', function ($q) use ($request) {
                        $q->where('name', 'LIKE', '%' . $request->search . '%');
                    });
            })
            ->latest()
            ->paginate($pagination);
    }

    public function count(): int
    {
        return $this->model->query()->count();
    }

    public function getAvailableStudents(string $search = null, int $limit = 10): Collection
    {
        return Student::query()
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->whereDoesntHave('rfid')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getByRfidNumber(string $rfid): ?Rfid
    {
        return $this->model->query()
            ->where('rfid', $rfid)
            ->first();
    }

    public function getUnassignedByRfidNumber(string $rfid): ?Rfid
    {
        return $this->model->query()
            ->where('rfid', $rfid)
            ->whereNull('student_id')
            ->first();
    }

    public function getByStudentId(string $studentId): ?Rfid
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->first();
    }

    public function getActiveByRfidNumber(string $rfid): ?Rfid
    {
        return $this->model->query()
            ->with(['student.user'])
            ->where('rfid', $rfid)
            ->where('status', RfidStatusEnum::ACTIVE->value)
            ->first();
    }
}
