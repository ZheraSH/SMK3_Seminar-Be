<?php

    namespace App\Contracts\Repositories;

    use App\Contracts\Interfaces\ClassroomInterface;
    use App\Models\Classroom;

    class ClassroomRepository extends BaseRepository implements ClassroomInterface
    {
        public function __construct(Classroom $Classroom)
        {
            $this->model = $Classroom;
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
    }
