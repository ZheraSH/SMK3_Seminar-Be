<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SchoolYearRepository extends BaseRepository implements SchoolYearInterface
{
    protected Model $model;

    public function __construct(SchoolYear $model)
    {
        $this->model = $model;
    }

    public function get(): mixed
    {
        return $this->model->orderByDesc('created_at')->get();
    }

    public function show(mixed $id): mixed
    {
        return $this->model->withTrashed()->find($id);
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): mixed
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(mixed $id): mixed
    {
        $model = $this->model->findOrFail($id);
        return $model->delete();
    }

    public function restore(string $id): mixed
    {
        $data = $this->model->onlyTrashed()->find($id);
        if ($data) {
            $data->restore();
        }
        return $data;
    }

  
    public function paginate(): mixed
    {
        return $this->model->query()->latest()->paginate(8);
    }

   public function search(Request $request, int $pagination = 10): mixed
    {
    
        return $this->model->query()
        ->when($request->keyword, function ($query) use ($request) {
            $query->where('school_year', 'like', '%' . $request->keyword . '%');
        })
        ->when($request->active, function ($query) use ($request) {
            $query->where('active', $request->active);
        })
        ->orderByDesc('created_at')
        ->paginate($pagination);
    }

}
