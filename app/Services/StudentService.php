<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Enums\RoleEnum;
use App\Enums\UploadDiskEnum;
use App\Models\Student;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentService
{
    use UploadTrait;

    private UserInterface $user;
    private StudentInterface $student;

    public function __construct(UserInterface $user, StudentInterface $student)
    {
        $this->user = $user;
        $this->student = $student;
    }

    /* ==================== CRUD ==================== */
    public function store(StoreStudentRequest $request): Student
    {
        $data = $request->validated();

        // Upload image jika ada
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->upload(UploadDiskEnum::STUDENT->value, $request->file('image'));
        }

        // Buat user baru
        $userData = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nisn']),
        ];

        $user = $this->user->store($userData);
        $user->assignRole(RoleEnum::STUDENT->value);

        // Simpan data student
        $studentData = collect($data)->except(['name', 'email'])->toArray();
        $studentData['id'] = (string) Str::uuid();
        $studentData['user_id'] = $user->id;

        return $this->student->store($studentData);
    }

    public function update(Student $student, UpdateStudentRequest $request): Student
    {
        $data = $request->validated();

        // Pisahkan data user dan student
        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nisn']),
        ];

        $studentData = collect($data)->except(['name', 'email', 'role'])->toArray();

        // Upload image jika ada file baru
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $studentData['image'] = $this->handleUpload($student->image, $request->file('image'));
        } else {
            $studentData['image'] = $student->image;
        }

        // Update data user dan student
        $this->user->update($student->user_id, $userData);
        return $this->student->update($student->id, $studentData);
    }

    public function delete(Student $student): void
    {
        if ($student->image) {
            $this->remove($student->image);
        }

        $this->student->delete($student->id);
        $this->user->delete($student->user_id);
    }

    /* ==================== DASHBOARD ==================== */
    public function dashboard(string $studentUuid): array
    {
        $student = $this->findByUuidOrFail($studentUuid);

        // contoh data dummy
        return [
            'total_attendance' => 40,
            'total_violation' => 2,
            'average_score' => 85.4,
            'student_name' => $student->user->name,
        ];
    }

    public function historyAttendance(string $studentUuid): array
    {
        $this->findByUuidOrFail($studentUuid);

        return [
            ['date' => '2025-10-10', 'status' => 'Hadir'],
            ['date' => '2025-10-11', 'status' => 'Izin'],
        ];
    }

    public function lessonSchedule(string $studentUuid): array
    {
        $this->findByUuidOrFail($studentUuid);

        return [
            ['day' => 'Senin', 'subject' => 'Matematika', 'time' => '07:00 - 08:30'],
            ['day' => 'Selasa', 'subject' => 'Bahasa Inggris', 'time' => '09:00 - 10:30'],
        ];
    }

    public function classStudent(string $studentUuid): array
    {
        $this->findByUuidOrFail($studentUuid);

        return [
            'name' => 'XII RPL 2',
            'homeroom_teacher' => 'Bapak Andi',
            'total_students' => 32,
        ];
    }

    public function detailProfile(string $studentUuid): Student
    {
        return $this->findByUuidOrFail($studentUuid);
    }

    /* ==================== Query ==================== */
    public function topPoints(int $limit = 5): Collection
    {
        return $this->student->getAll(request())
            ->sortByDesc('point')
            ->take($limit);
    }

    public function studentsWithoutClassroom(Request $request): Collection
    {
        return $this->student->getAll($request)
            ->filter(fn($student) => $student->classroomStudents->isEmpty());
    }

    /* ==================== Helper ==================== */
    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) {
            $this->remove($oldFile);
        }

        return $this->upload(UploadDiskEnum::STUDENT->value, $file);
    }

    private function findByUuidOrFail(string $uuid): Student
    {
        $student = $this->student->findByUuid($uuid);

        if (!$student) {
            throw new ModelNotFoundException("Student with UUID {$uuid} not found.");
        }

        return $student;
    }
}