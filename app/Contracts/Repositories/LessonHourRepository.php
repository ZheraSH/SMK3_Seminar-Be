<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Models\LessonHour;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class LessonHourRepository implements LessonHourInterface
{
    use PaginationTrait;

    protected $model;

    public function __construct(LessonHour $lessonHour)
    {
        $this->model = $lessonHour;
    }

    public function get(): mixed
    {
        return $this->model->orderBy('start')->get();
    }

    public function paginate($perPage = 10): mixed
    {
        return $this->model->orderBy('start')->paginate($perPage);
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): mixed
    {
        $lessonHour = $this->model->findOrFail($id);
        $lessonHour->update($data);
        return $lessonHour;
    }

    public function show($id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function delete($id): bool
    {
        $lessonHour = $this->model->findOrFail($id);
        return $lessonHour->delete();
    }

        public function search(Request $request): mixed
    {
        $keyword = $request->input('keyword');

        return $this->model
            ->when($keyword, function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('start', 'like', "%{$keyword}%")
                      ->orWhere('end', 'like', "%{$keyword}%");
            })
            ->orderBy('start')
            ->paginate($request->input('per_page', 10));
    }
}
