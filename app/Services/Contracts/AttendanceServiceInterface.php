<?php

namespace App\Services\Contracts;

use App\Models\Attendance;

interface AttendanceServiceInterface
{
    /** @return \Illuminate\Database\Eloquent\Collection<Attendance> */
    public function listAll(): object;

    public function create(array $payload): Attendance;

    public function findById(int $id): ?Attendance;

    public function update(Attendance $attendance, array $payload): Attendance;

    public function delete(Attendance $attendance): bool;
}


