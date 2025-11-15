<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Enums\RoleEnum;
use App\Enums\StudentStatusEnum;
use App\Enums\UploadDiskEnum;
use App\Models\Student;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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

    public function store(StoreStudentRequest $request): Student
    {
        $data = $request->validated();
        $data['status'] = StudentStatusEnum::ACTIVE->value;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->upload(UploadDiskEnum::STUDENT->value, $request->file('image'));
        }

        $userData = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nisn']),
        ];

        $user = $this->user->store($userData);
        $user->assignRole(RoleEnum::STUDENT->value);

        $studentData = collect($data)->except(['name', 'email'])->toArray();
        $studentData['id'] = (string) Str::uuid();
        $studentData['user_id'] = $user->id;

        $student = $this->student->store($studentData);
        return $this->student->show($student->id);
    }

    public function update(string $id, UpdateStudentRequest $request): Student
    {
        $student = $this->student->show($id);
        $data = $request->validated();

        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
        ];

        if (isset($data['nisn']) && $data['nisn'] !== $student->nisn) {
            $userData['password'] = Hash::make($data['nisn']);
        }

        $this->user->update($student->user_id, $userData);

        $studentData = collect($data)->except(['name', 'email', 'role'])->toArray();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $studentData['image'] = $this->handleUpload($student->image, $request->file('image'));
        }

        $this->student->update($student->id, $studentData);

        return $this->student->show($id);
    }

    public function delete(string $id): bool
    {
        $student = $this->student->show($id);

        if ($student->image) {
            $this->remove($student->image);
        }

        $this->student->delete($student->id);
        $this->user->delete($student->user_id);

        return true;
    }

    public function show(string $id): Student
    {
        return $this->student->show($id);
    }

    public function getWithFilter(Request $request, int $pagination = 8): LengthAwarePaginator
    {
        return $this->student->search($request, $pagination);
    }

    public function getActiveClassroom(string $studentId)
    {
        try {
            Log::info('Getting active classroom for student', ['student_id' => $studentId]);
            
            $student = $this->student->showWithActiveClassroom($studentId);
            
            Log::info('Student classroom students data', [
                'student_id' => $studentId,
                'classroom_students_count' => $student->classroomStudents->count(),
                'classroom_students' => $student->classroomStudents->map(function($cs) {
                    return [
                        'id' => $cs->id,
                        'classroom_id' => $cs->classroom_id,
                        'status' => $cs->status,
                        'classroom_name' => $cs->classroom->name ?? null,
                    ];
                })->toArray()
            ]);

            $activeClassroom = $student->classroomStudents
                ->where('status', StudentStatusEnum::ACTIVE->value)
                ->first()?->classroom;

            Log::info('Active classroom result', [
                'student_id' => $studentId,
                'active_classroom_found' => !is_null($activeClassroom),
                'active_classroom_name' => $activeClassroom->name ?? null
            ]);

            return $activeClassroom;

        } catch (\Exception $e) {
            Log::error('Error getting active classroom', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getActiveClassroomStudent(string $studentId)
    {
        try {
            $student = $this->student->showWithActiveClassroom($studentId);
            
            return $student->classroomStudents
                ->where('status', StudentStatusEnum::ACTIVE->value)
                ->first();
                
        } catch (\Exception $e) {
            Log::error('Error getting active classroom student', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function hasActiveClassroom(string $studentId): bool
    {
        return !is_null($this->getActiveClassroom($studentId));
    }

    public function getActiveStudents()
    {
        return $this->student->getActiveStudents();
    }

    public function countActiveStudents(): int
    {
        return $this->student->countActiveStudents();
    }

    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) {
            $this->remove($oldFile);
        }
        return $this->upload(UploadDiskEnum::STUDENT->value, $file);
    }
}