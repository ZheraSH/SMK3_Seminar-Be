<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Services\Contracts\AttendanceServiceInterface;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceServiceInterface $attendanceService) {}

    public function index()
    {
        $data = $this->attendanceService->listAll();
        return ResponseHelper::success($data, 'Attendance list retrieved');
    }

    public function store(StoreAttendanceRequest $request)
    {
        $attendance = $this->attendanceService->create($request->validated());
        return ResponseHelper::success($attendance, 'Attendance created');
    }

    public function show($id)
    {
        $attendance = $this->attendanceService->findById((int) $id);
        if (!$attendance) {
            return ResponseHelper::notFound('Attendance not found');
        }
        return ResponseHelper::success($attendance, 'Attendance retrieved');
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return ResponseHelper::notFound('Attendance not found');
        }
        $updated = $this->attendanceService->update($attendance, $request->validated());
        return ResponseHelper::success($updated, 'Attendance updated');
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return ResponseHelper::notFound('Attendance not found');
        }
        if (!$this->attendanceService->delete($attendance)) {
            return ResponseHelper::error('Failed to delete attendance');
        }
        return ResponseHelper::success(null, 'Attendance deleted');
    }
}


