<?php

namespace App\Services\Contracts;

use App\Models\Attendance;

interface RfidAttendanceServiceInterface
{
    public function tap(string $rfid, ?string $timestamp = null): Attendance;
}


