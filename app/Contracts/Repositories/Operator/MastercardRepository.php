<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\MastercardInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Models\Mastercard;
use Illuminate\Http\Request;

class MastercardRepository extends BaseRepository implements MastercardInterface
{
    public function __construct(Mastercard $mastercard)
    {
        $this->model = $mastercard;
    }

    public function get(): mixed
    {
        return $this->model->latest()->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function search(Request $request, int $pagination = 10): mixed
    {
        return $this->model->query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('rfid', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($pagination);
    }

    public function findByRfid(string $rfid): ?Mastercard
    {
        return $this->model->where('rfid', $rfid)->first();
    }
}
