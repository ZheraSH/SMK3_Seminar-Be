<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearRepository extends BaseRepository implements SchoolYearInterface
{
    public function __construct(SchoolYear $schoolYear)
    {
        $this->model = $schoolYear;
    }

    public function get(): mixed
    {
        return $this->model->all();
    }

    public function show(mixed $id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
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

    public function search(Request $request, int $pagination = 12): mixed
    {
        return $this->model
            ->when($request->keyword, fn($q) => 
                $q->where('name', 'like', "%{$request->keyword}%")
            )
            ->when($request->active !== null, fn($q) =>
                $q->where('active', $request->active)
            )
            ->latest()
            ->paginate($pagination);
    }

    public function active(): mixed
    {
        return $this->model->where('active', true)->first();
    }

    public function setActive($id): mixed
    {
        return $this->show($id)->update(['active' => true]);
    }

    public function unsetAll(): mixed
    {
        return $this->model->query()->update(['active' => false]);
    }

    public function storeAuto(): mixed
{
    $this->unsetAll();

    $latest = $this->model
        ->orderByDesc('name')
        ->first();

    if (!$latest) {
        $start = date('Y');
        $end = $start + 1;
    } else {

        [$start, $end] = explode('/', $latest->name);

        $start = (int)$start + 1; 
        $end = $start + 1;
    }

    return $this->model->create([
        'name'   => "{$start}/{$end}",
        'active' => true,
    ]);
}

}