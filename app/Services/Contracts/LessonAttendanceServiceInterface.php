<?php

namespace App\Services\Contracts;

use App\Models\AttendanceJournal;

interface LessonAttendanceServiceInterface
{
    /** @return \Illuminate\Database\Eloquent\Collection<AttendanceJournal> */
    public function listAll(): object;

    public function mark(int $teacherJournalId, int $studentClassroomId, int $lessonHourId, string $status, ?string $date = null): AttendanceJournal;
}


