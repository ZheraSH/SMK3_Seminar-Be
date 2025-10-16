<?php

namespace App\Contracts\Interfaces;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentInterface
{
    public function getAll(Request $request): LengthAwarePaginator|Collection;
    public function findByUuid(string $uuid): Student;
    public function store(array $data): Student;
    public function update(string $uuid, array $data): Student;
    public function delete(string $uuid): bool;
}
