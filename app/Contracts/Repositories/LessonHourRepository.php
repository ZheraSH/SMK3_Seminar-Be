<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Models\LessonHour;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class LessonHourRepository extends BaseRepository implements LessonHourInterface
{
    use PaginationTrait;

    public function __construct(LessonHour $lessonHour)
    {
        $this->model = $lessonHour;
    }

    public function get(): mixed
    {
        return $this->model->query()->orderBy('start')->get();
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
        return $this->model->query()->orderBy('start')->paginate(10);
    }

    public function search(Request $request, int $pagination = 10): mixed
    {
        return $this->model->query()
            ->when($request->keyword, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->keyword . '%')
                      ->orWhere('start', 'like', '%' . $request->keyword . '%')
                      ->orWhere('end', 'like', '%' . $request->keyword . '%');
            })
            ->orderBy('start')
            ->paginate($pagination);
    }

    /**
     * Get last lesson hour by start time
     */
    public function getLastLessonHour(): mixed
    {
        return $this->model->query()->orderBy('start', 'desc')->first();
    }
}