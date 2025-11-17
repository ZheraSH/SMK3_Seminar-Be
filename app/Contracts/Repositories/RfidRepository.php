<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\RfidInterface;
use App\Enums\RfidStatusEnum;
use App\Models\Rfid;
use Illuminate\Http\Request;

class RfidRepository extends BaseRepository implements RfidInterface
{
    public function __construct(Rfid $rfid)
    {
        $this->model = $rfid;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->latest()
            ->paginate(10);
    }

    public function search(Request $request, int $pagination = 10): mixed
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

    public function getByStudentId(string $studentId): mixed
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->first();
    }

    public function getByRfidNumber(string $rfid): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->where('rfid', $rfid)
            ->first();
    }

    public function used(Request $request): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->where('status', RfidStatusEnum::ACTIVE->value)
            ->whereNotNull('student_id')
            ->get();
    }

    public function notUsed(Request $request): mixed
    {
        return $this->model->query()
            ->with(['student.user'])
            ->where(function($query) {
                $query->where('status', RfidStatusEnum::INACTIVE->value)
                      ->orWhereNull('student_id');
            })
            ->get();
    }
}