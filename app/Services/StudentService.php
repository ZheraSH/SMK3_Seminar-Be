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

        $studentData = collect($data)->except(['name','email'])->toArray();
        $studentData['id'] = (string) Str::uuid();
        $studentData['user_id'] = $user->id;

        return $this->student->store($studentData);
    }

    public function update(Student $student, UpdateStudentRequest $request): ?Student
    {
        if (!$student) return null;
    
        $data = $request->validated();
    
        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nisn']),
        ];
    
        $studentData = collect($data)->except(['name','email','role'])->toArray();
    
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $studentData['image'] = $this->handleUpload($student->image, $request->file('image'));
        }
    
        $this->user->update($student->user_id, $userData);
        $this->student->update($student->id, $studentData);
    
        return $student->fresh(['user','religion']);
    }

    public function delete(Student $student): bool
    {
        if (!$student) return false;

        if ($student->image) $this->remove($student->image);

        $this->student->delete($student->id);
        $this->user->delete($student->user_id);

        return true;
    }

    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) $this->remove($oldFile);
        return $this->upload(UploadDiskEnum::STUDENT->value, $file);
    }
    public function getWithFilter(Request $request, int $pagination = 8): mixed
    {
        return $this->student->search($request, $pagination);
    }
}