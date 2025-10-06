<?php

namespace App\Services;

use App\Enums\AttendanceEnum;
use App\Models\AttendanceJournal;
use App\Services\Contracts\LessonAttendanceServiceInterface;
use Carbon\Carbon;

class LessonAttendanceService implements LessonAttendanceServiceInterface
{
    public function listAll(): object
    {
        return AttendanceJournal::with(['teacherJournal', 'classroomStudent', 'lessonHour'])->get();
    }

    public function mark(int $teacherJournalId, int $studentClassroomId, int $lessonHourId, string $status, ?string $date = null): AttendanceJournal
    {
        $journal = AttendanceJournal::create([
            'teacher_journal_id' => $teacherJournalId,
            'student_classroom_id' => $studentClassroomId,
            'lesson_hour_id' => $lessonHourId,
            'status' => $status,
            'date' => $date ? Carbon::parse($date) : Carbon::today(),
        ]);

        return $journal->load(['teacherJournal', 'classroomStudent', 'lessonHour']);
    }
}


