<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\EmployeeInterface;
use App\Models\Employee;
use App\Traits\PaginationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EmployeeRepository extends BaseRepository implements EmployeeInterface
{
    use PaginationTrait;

    public function __construct(Employee $employee)
    {
        $this->model = $employee;
    }

    public function get(): Collection
    {
        return $this->model->query()
            ->with(['user.roles', 'religion', 'subjects'])
            ->get();
    }

    public function store(array $data): Employee
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): Employee
    {
        return $this->model->query()
            ->with(['user.roles', 'religion', 'subjects'])
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

    public function paginate(): mixed
    {
        return $this->model->query()
            ->with(['user.roles', 'religion', 'subjects'])
            ->latest()
            ->paginate(8);
    }

    public function search(Request $request, int $pagination = 8): mixed
    {
        return $this->model->query()
            ->with(['user.roles', 'religion', 'subjects'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($sub) use ($request) {
                        $sub->where('name', 'LIKE', '%' . $request->search . '%');
                    })
                    ->orWhere('NIP', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->when($request->role, function ($query) use ($request) {
                $roles = explode(',', $request->role);
                $query->whereHas('user.roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            })
            ->when($request->gender, function ($query) use ($request) {
                $genders = explode(',', $request->gender);
                $query->whereIn('gender', $genders);
            })
            ->when($request->subject, function ($query) use ($request) {
                $subjectNames = explode(',', $request->subject);
                $query->whereHas('subjects', function ($q) use ($subjectNames) {
                    $q->whereIn('name', $subjectNames);
                });
            })
            ->latest()
            ->paginate($pagination);
    }

    public function count(): int
    {
        return $this->model->query()->count();
    }

    public function countByRoles(array $roles): int
    {
        return $this->model->query()
            ->whereHas('user.roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            })
            ->count();
    }

    public function getByUserId(string $userId): ?Employee
        {
            return $this->model
                ->where('user_id', $userId)
                ->first();
        }
}