<?php

namespace App\Contracts\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceGlobalInterface
{
    public function get(): Collection;

    public function getPaginated(array $filters): LengthAwarePaginator;

    public function search(array $filters): LengthAwarePaginator;

    public function getGlobalStats(array $filters): array;

    public function getMonthlyTrend(array $filters): array;

    public function count(): int;
}
