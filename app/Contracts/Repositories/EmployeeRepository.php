<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\EmployeeInterface;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeRepository extends BaseRepository implements EmployeeInterface
{
    public function __construct(Employee $employee)
    {
        $this->model = $employee;
    }

    public function get(): mixed
    {
        return $this->model->query()->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->model->query()->latest()->paginate(8);
    }

    // public function search(Request $request, int $pagination = 8): mixed
    // {
    //     return $this->model->query()
    //         ->when($request->search, function ($query) use ($request) {
    //             $query->where(function($q) use ($request) {
    //                 $q->whereHas('user', function ($sub) use ($request) {
    //                 $sub->where('name', 'LIKE', '%' . $request->search . '%');
    //             })
    //             ->orWhere('NIP', 'LIKE', '%' . $request->search . '%');
    //         });
    //         })
    //         ->when($request->role, function ($query) use ($request) {
    //             $query->where('role', $request->role);
    //         })
    //         ->when($request->gender, function ($query) use ($request) {
    //             $query->where('gender', $request->gender);
    //         })
    //         ->when($request->subject_id, function ($query) use ($request) {
    //             $query->where('subject_id', $request->subject_id);
    //         })
    //         ->latest()
    //         ->paginate($pagination);
    // }
    
    public function search(Request $request, int $pagination = 8): mixed
{
    return $this->model->query()
        ->when($request->search, function ($query) use ($request) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            })
            ->orWhere('NIP', 'LIKE', '%' . $request->search . '%');
        })
        ->when($request->gender, function ($query) use ($request) {
            $query->where('gender', $request->gender);
        })
        // untuk sementara skip role_id & subject_id
        ->latest()
        ->paginate($pagination);
}

    public function count(): mixed
    {
        return $this->model->query()->count();
    }
}
