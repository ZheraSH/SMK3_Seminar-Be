<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceRepository extends BaseRepository implements AttendanceInterface
{
    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->with(['student', 'classroomStudent.classroom', 'rfid'])
            ->latest()
            ->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()
            ->with(['student', 'classroomStudent.classroom', 'rfid'])
            ->findOrFail($id);
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
        return $this->model->query()
            ->with(['student', 'classroomStudent.classroom', 'rfid'])
            ->latest()
            ->paginate(10);
    }

    public function search(Request $request, int $pagination = 7): mixed
    {
        return $this->model->query()
            ->with(['student', 'classroomStudent.classroom', 'rfid'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->when($request->date, function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->latest()
            ->paginate($pagination);
    }

    public function getByStudentAndDate(string $studentId, string $date): mixed
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->first();
    }

    public function getTodayByStudent(string $studentId): mixed
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->whereDate('date', now()->toDateString())
            ->first();
    }

    public function getByClassroomAndDate(string $classroomId, string $date): mixed
    {
        return $this->model->query()
            ->whereHas('classroomStudent', function ($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
            })
            ->whereDate('date', $date)
            ->with(['student'])
            ->get();
    }

    public function getStudentMonthlyAttendance(string $studentId, string $month, string $year): mixed
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    }

    public function recordTap(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    /**
     * Get attendance by specific date
     */
    public function getByDate(string $date): mixed
    {
        return $this->model->query()
            ->with(['student', 'classroomStudent.classroom', 'rfid'])
            ->whereDate('date', $date)
            ->get();
    }
}