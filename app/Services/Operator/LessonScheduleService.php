<?php
namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\LessonScheduleRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Http\Requests\Operator\StoreLessonSchedulesRequest;
use App\Http\Requests\Operator\UpdateLessonSchedulesRequest;
use App\Models\LessonSchedule;
use Exception;
use Illuminate\Support\Facades\DB;

class LessonScheduleService
{
    private LessonScheduleRepository $lessonScheduleRepository;
    private ClassroomRepository $classroomRepository;

    public function __construct(LessonScheduleRepository $lessonScheduleRepository, ClassroomRepository $classroomRepository)
    {
        $this->lessonScheduleRepository = $lessonScheduleRepository;
        $this->classroomRepository = $classroomRepository;
    }

    public function store(StoreLessonSchedulesRequest $request): LessonSchedule
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
    
            $this->validateScheduleConflict($data);
    
            $schedule = $this->lessonScheduleRepository->store($data);
    
            return $this->lessonScheduleRepository->show($schedule->id);
        });
    }    

    public function update(string $id, UpdateLessonSchedulesRequest $request): LessonSchedule
    {
        $existing = $this->lessonScheduleRepository->show($id);
    
        $data = array_merge(
            $existing->only([
                'classroom_id',
                'day',
                'lesson_hour_id',
                'teacher_id',
                'subject_id',
            ]),
            $request->validated()
        );
    
        DB::transaction(function () use ($id, $data) {
            $this->validateScheduleConflict($data, $id);
            $this->lessonScheduleRepository->update($id, $data);
        });
    
        return $this->lessonScheduleRepository->show($id);
    }    

    public function show(string $id): LessonSchedule
    {
        return $this->lessonScheduleRepository->show($id);
    }

    public function delete(string $id): bool
    {
        return $this->lessonScheduleRepository->delete($id);
    }

    public function getLessonScheduleClassroomAndDay(string $classroomId, string $day): array
    {
        return [
            'classroom' => $this->classroomRepository->show($classroomId),
            'day' => $day,
            'schedules' => $this->lessonScheduleRepository->getLessonScheduleClassroomAndDay($classroomId, $day),
        ];
    }

    private function validateScheduleConflict(array $data, ?string $excludeId = null): void
    {
        if ($this->lessonScheduleRepository->checkClassroomConflict($data['classroom_id'], $data['day'], $data['lesson_hour_id'], $excludeId)) {
            throw new Exception('Kelas sudah memiliki jadwal di hari dan jam yang sama.');
        }

        if ($this->lessonScheduleRepository->checkTeacherConflict($data['teacher_id'], $data['day'], $data['lesson_hour_id'], $excludeId)) {
            throw new Exception('Guru sudah memiliki jadwal mengajar di hari dan jam yang sama.');
        }
    }
}