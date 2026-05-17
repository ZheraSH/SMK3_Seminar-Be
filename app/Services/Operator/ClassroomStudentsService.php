<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\ClassroomStudentsRepository;
use App\Imports\ClassroomStudentImport;
use App\Models\Classroom;
use App\Models\LevelClass;
use App\Models\SchoolYear;
use App\Enums\StudentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use RuntimeException;

class ClassroomStudentsService
{
    private ClassroomStudentsRepository $classroomStudentsRepository;

    public function __construct(ClassroomStudentsRepository $classroomStudentsRepository)
    {
        $this->classroomStudentsRepository = $classroomStudentsRepository;
    }

    public function getByClassroom(string $classroomId, Request $request)
    {
        return $this->classroomStudentsRepository->getByClassroom($classroomId, $request);
    }

    public function getAvailableStudents(string $classroomId, Request $request)
    {
        return $this->classroomStudentsRepository->getAvailableStudents(
            $classroomId,
            $request->search,
            $request->limit ?? 10
        );
    }

    public function addStudents(string $classroomId, array $studentIds): Collection
    {
        $this->classroomStudentsRepository->addStudents($classroomId, $studentIds);

        return $this->classroomStudentsRepository->getByClassroom($classroomId, new Request(['limit' => 1000]))
            ->getCollection();
    }

    public function removeStudent(string $classroomId, string $studentId): Collection
    {
        $this->classroomStudentsRepository->removeStudent($classroomId, $studentId);

        return $this->classroomStudentsRepository->getByClassroom($classroomId, new Request(['limit' => 1000]))
            ->getCollection();
    }

    public function promoteClass(string $oldClassroomId, ?string $homeroomTeacherId = null): array
    {
        return DB::transaction(function () use ($oldClassroomId, $homeroomTeacherId) {
            $sourceClassroom = Classroom::with(['levelClass', 'schoolYear', 'major'])->findOrFail($oldClassroomId);

            $targetSchoolYear = SchoolYear::where('id', '!=', $sourceClassroom->school_year_id)
                ->orderByDesc('name')
                ->first();

            if (! $targetSchoolYear) {
                throw new RuntimeException('Tidak ada tahun ajaran lain yang tersedia untuk promosi.', 422);
            }

            $currentLevel = $sourceClassroom->levelClass;

            $nextLevel = LevelClass::where('level_order', '>', $currentLevel->level_order)
                ->orderBy('level_order')
                ->first();

            if (! $nextLevel) {
                throw new RuntimeException("Kelas {$currentLevel->name} adalah tingkat tertinggi, tidak dapat dinaikkan.", 422);
            }

            $activeStudents = $this->classroomStudentsRepository->getActiveByClassroom($oldClassroomId);

            if ($activeStudents->isEmpty()) {
                throw new RuntimeException('Tidak ada siswa aktif di kelas ini.', 422);
            }

            $studentIds = $activeStudents->pluck('student_id')->all();
            $activeStudentIds = $activeStudents->pluck('id')->all();

            $newName = preg_replace(
                '/^' . preg_quote($currentLevel->name, '/') . '\b/',
                $nextLevel->name,
                $sourceClassroom->name,
                1
            );

            $existingDestination = Classroom::where('level_class_id', $nextLevel->id)
                ->where('major_id', $sourceClassroom->major_id)
                ->where('school_year_id', $targetSchoolYear->id)
                ->where('name', $newName)
                ->first();

            if ($existingDestination) {
                $destinationClassroom = $existingDestination;
                if ($homeroomTeacherId) {
                    $destinationClassroom->update(['homeroom_teacher_id' => $homeroomTeacherId]);
                }
            } else {
                $destinationClassroom = Classroom::create([
                    'id' => (string) Str::uuid(),
                    'name' => $newName,
                    'slug' => Str::slug($newName) . '-' . Str::random(4),
                    'level_class_id' => $nextLevel->id,
                    'major_id' => $sourceClassroom->major_id,
                    'school_year_id' => $targetSchoolYear->id,
                    'homeroom_teacher_id' => $homeroomTeacherId,
                ]);
            }

            $now = Carbon::now()->toDateTimeString();

            $this->classroomStudentsRepository->bulkDeactivate($activeStudentIds, $now);

            $newRows = collect($studentIds)->map(fn ($sid) => [
                'id' => (string) Str::uuid(),
                'classroom_id' => $destinationClassroom->id,
                'student_id' => $sid,
                'status' => StudentStatusEnum::ACTIVE->value,
                'active_unique_guard' => $sid,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            $this->classroomStudentsRepository->bulkUpsertActive($newRows);

            $logRows = collect($studentIds)->map(fn ($sid) => [
                'id' => (string) Str::uuid(),
                'student_id' => $sid,
                'from_classroom_id' => $oldClassroomId,
                'to_classroom_id' => $destinationClassroom->id,
                'from_school_year_id' => $sourceClassroom->school_year_id,
                'to_school_year_id' => $targetSchoolYear->id,
                'from_level_name' => $currentLevel->name,
                'to_level_name' => $nextLevel->name,
                'promoted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            DB::table('promotion_logs')->upsert(
                $logRows,
                ['student_id', 'from_classroom_id', 'to_school_year_id'],
                ['to_classroom_id', 'to_level_name', 'promoted_at', 'updated_at']
            );

            return [
                'source_classroom' => $sourceClassroom,
                'destination_classroom' => $destinationClassroom->fresh(['levelClass', 'schoolYear', 'major']),
                'from_level' => $currentLevel->name,
                'to_level' => $nextLevel->name,
                'from_school_year' => $sourceClassroom->schoolYear->name,
                'to_school_year' => $targetSchoolYear->name,
                'students_promoted' => count($studentIds),
            ];
        });
    }

    public function importStudents(string $classroomId, mixed $file): array
    {
        $storedPath = $file->store('imports/tmp', 'local');
        $fullPath = storage_path('app/' . $storedPath);
        $import = new ClassroomStudentImport($classroomId);

        try {
            Excel::import($import, $fullPath);

            return [
                'failed' => false,
                'imported_count' => $import->importedCount,
                'errors' => [],
            ];
        } catch (ValidationException $e) {
            $grouped = [];
            foreach ($e->failures() as $failure) {
                foreach ($failure->errors() as $error) {
                    $key = $failure->attribute() . '|' . $error;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'kolom'   => $failure->attribute(),
                            'message' => $error,
                            'rows'    => [],
                        ];
                    }
                    $grouped[$key]['rows'][] = $failure->row();
                }
            }
            return [
                'failed' => true,
                'imported_count' => 0,
                'errors' => array_values($grouped),
            ];
        } catch (\RuntimeException $e) {
            return [
                'failed' => true,
                'imported_count' => 0,
                'errors' => $import->getErrors(),
            ];
        } finally {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
