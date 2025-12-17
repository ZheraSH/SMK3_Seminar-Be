<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\EmployeeInterface;
use App\Contracts\Repositories\BaseRepository;
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

    protected function baseQuery()
    {
        return $this->model->query()->with([
            'user.roles',
            'religion',
        ]);
    }

    public function get(): Collection
    {
        return $this->baseQuery()->get();
    }

    public function store(array $data): Employee
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): Employee
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    public function paginate(int $perPage = 15): mixed
    {
        return $this->baseQuery()
            ->latest()
            ->paginate($perPage);
    }

    public function search(Request $request, int $pagination = 15): mixed
    {
        return $this->baseQuery()
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) =>
                        $u->where('name', 'LIKE', "%{$search}%")
                    )
                    ->orWhere('nip', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->role, function ($query) use ($request) {
                $roles = explode(',', $request->role);
                $query->whereHas('user.roles', fn ($q) =>
                    $q->whereIn('name', $roles)
                );
            })
            ->latest()
            ->paginate($pagination);
    }

    public function count(): int
    {
        return $this->model->count();
    }
}