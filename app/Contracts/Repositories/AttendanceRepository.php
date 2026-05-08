<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository extends BaseRepository implements AttendanceInterface
{
    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    public function get(): Collection
    {
        return $this->model->query()->latest()->get();
    }

    public function store(array $data): Attendance
    {
        return $this->model->query()->create($data);
    }

    public function storeBulk(array $data): bool
    {
        return $this->model->query()->insert($data);
    }

    public function show($id): Attendance
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function updateOrCreate(array $attributes, array $data): Attendance
    {
        return $this->model->updateOrCreate($attributes, $data);
    }

    public function updateOrInsert(array $attributes, array $data): mixed
    {
        $query = $this->model->query();

        foreach ($attributes as $key => $value) {
            if ($key === 'created_at' || $key === 'updated_at') {
                $query->whereDate($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        $record = $query->first();

        if ($record) {
            $record->update($data);
            return $record;
        }

        unset($attributes['created_at'], $attributes['updated_at']);

        return $this->model->query()->create($data);
    }

    public function delete($id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    // =========================================================================
    //  TEACHER — Cross-Check Per Jam Pelajaran
    // =========================================================================


    public function getByScheduleAndDate(string $lessonScheduleId, string $date): Collection
    {
        return $this->model
            ->where('lesson_schedule_id', $lessonScheduleId)
            ->whereDate('date', $date)
            ->get();
    }

    public function getByStudentLesson(string $studentId, string $date, int $lessonOrder): ?Attendance
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->first();
    }

    public function getByClassroomAndDate(string $classroomId, string $date): Collection
    {
        return $this->model
            ->whereHas('student.classroomStudents', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)
                    ->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value);
            })
            ->whereDate('date', $date)
            ->final()
            ->get();
    }


    public function getSubmissionInfo(string $lessonScheduleId, string $date, int $lessonOrder): ?Attendance
    {
        return $this->model
            ->where('lesson_schedule_id', $lessonScheduleId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->orderByDesc('updated_at')
            ->first();
    }

    public function getFinalAttendancesForStudent(string $studentId, string $date): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->orderBy('lesson_order')
            ->get();
    }

    // =========================================================================
    //  LOCK & PERMISSION
    // =========================================================================

    public function isAttendanceLocked(string $studentId, string $date, int $lessonOrder): bool
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->where('is_locked', true)
            ->exists();
    }


    public function lockAttendance(string $studentId, string $date, int $lessonOrder, string $status, string $permissionId, ?string $lessonScheduleId = null, ?string $subjectId = null, ?string $teacherId = null, ?string $classroomStudentId = null): Attendance
    {
        $data = [
            'status' => $status,
            'is_locked' => true,
            'overridden_by_permission_id' => $permissionId,
            'is_final' => true
        ];

        if ($lessonScheduleId) $data['lesson_schedule_id'] = $lessonScheduleId;
        if ($subjectId) $data['subject_id'] = $subjectId;
        if ($teacherId) $data['teacher_id'] = $teacherId;
        if ($classroomStudentId) $data['classroom_student_id'] = $classroomStudentId;

        return $this->model->updateOrCreate(
            [
                'student_id' => $studentId,
                'date' => $date,
                'lesson_order' => $lessonOrder
            ],
            $data
        );
    }

    // =========================================================================
    //  COUNSELOR — Rekap Harian (dari cross-check)
    // =========================================================================

    public function countTotalStatusToday(string $date): array
    {
        return $this->model
            ->whereDate('date', $date)
            ->final()
            ->selectRaw("
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as hadir,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as izin,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as sakit,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as alpa
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::PERMISSION->value,
                AttendanceStatusEnum::SICK->value,
                AttendanceStatusEnum::ALPHA->value,
            ])
            ->first()
            ->toArray();
    }

    public function getStudentsWithHighAlpha(int $threshold, int $limit = 10): Collection
    {
        return $this->model
            ->where('status', AttendanceStatusEnum::ALPHA->value)
            ->final()
            ->selectRaw('student_id, COUNT(DISTINCT date) as total_alpha')
            ->groupBy('student_id')
            ->having('total_alpha', '>=', $threshold)
            ->orderByDesc('total_alpha')
            ->limit($limit)
            ->with(['student.classroomStudents' => function ($q) {
                $q->where('status', 'active')->with('classroom');
            }])
            ->get();
    }
}
