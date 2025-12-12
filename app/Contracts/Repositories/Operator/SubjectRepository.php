<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\SubjectInterface;
use App\Contracts\Repositories\BaseRepository;
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

    public function paginate(): mixed
    {
        return $this->model->latest()->paginate(12);
    }

    public function search(Request $request, int $perPage = 12): mixed
    {
        return $this->model->query()
            ->when($request->keyword, fn($q) => $q->where('name', 'like', "%{$request->keyword}%"))
            ->latest()
            ->paginate($perPage);
    }
    public function storeOrRestore(array $data)
    {

        $subject = Subject::withTrashed()
            ->where('name', $data['name'])
            ->first();

        if ($subject) {

            if ($subject->trashed()) {
                $subject->restore();
            }

            $subject->update($data);

            return $subject;
        }

        return Subject::create($data);
    }
}
