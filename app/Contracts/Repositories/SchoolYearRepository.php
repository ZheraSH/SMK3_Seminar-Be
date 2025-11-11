<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Models\SchoolYear;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class SchoolYearRepository extends BaseRepository implements SchoolYearInterface
{   
    use PaginationTrait;
    public function __construct(SchoolYear $schoolYear)
    {
        $this->model = $schoolYear;
    }

    public function get(): mixed
    {
        return $this->model->query()->get();
    }

    public function show(mixed $id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
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
        return $this->model->query()->latest()->paginate(12);
    }

   public function search(Request $request, int $pagination = 12): mixed
    {
    
        return $this->model->query()
           ->when($request->keyword, function ($query) use ($request) {
               $query->where('name', 'like', '%' . $request->keyword . '%');
        })
           ->when($request->active, function ($query) use ($request) {
               $query->where('active', $request->active);
        })
        ->latest()
        ->paginate($pagination);
    }

}
