<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SubjectInterface;
use App\Models\Subject;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class SubjectRepository extends BaseRepository implements SubjectInterface
{
    use PaginationTrait;
    public function __construct(Subject $subject)
    {
        $this->model = $subject;
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
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function paginate(int $perPage = 12): mixed
    {
        return $this->model->query()
        ->latest()
        ->paginate($perPage)
        ->withQueryString(); 
    }

  public function search(Request $request, int $pagination = 12): mixed
    {
        return $this->model->query()
            ->when($request->keyword, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->keyword . '%');
            })
            ->latest()
            ->paginate($pagination)
            ->withQueryString();
    }
}