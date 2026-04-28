<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Operator\StoreStudentRequest;
use App\Http\Requests\Operator\UpdateStudentRequest;
use App\Enums\RoleEnum;
use App\Enums\StudentStatusEnum;
use App\Enums\UploadDiskEnum;
use App\Enums\GenderEnum;
use App\Models\Student;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    use UploadTrait;

    private UserRepository $userRepository;
    private StudentRepository $studentRepository;

    public function __construct(UserRepository $userRepository, StudentRepository $studentRepository)
    {
        $this->userRepository = $userRepository;
        $this->studentRepository = $studentRepository;
    }

    public function store(StoreStudentRequest $request): Student
    {
        $data = $request->validated();
        $data['status'] = StudentStatusEnum::ACTIVE->value;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->upload(UploadDiskEnum::STUDENT->value, $request->file('image'));
        } else {
            $data['image'] = ($data['gender'] === GenderEnum::MALE->value) ? 'default_image/student-boy.png' : 'default_image/student-girl.png';
        }

        $userData = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nisn']),
        ];

        $user = $this->userRepository->store($userData);
        $user->assignRole(RoleEnum::STUDENT->value);

        $studentData = collect($data)->except(['name', 'email'])->toArray();
        $studentData['id'] = (string) Str::uuid();
        $studentData['user_id'] = $user->id;

        $student = $this->studentRepository->store($studentData);
        return $this->studentRepository->show($student->id);
    }

    public function update(string $id, UpdateStudentRequest $request): Student
    {
        $student = $this->studentRepository->show($id);
        $data = $request->validated();

        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
        ];

        if (isset($data['nisn']) && $data['nisn'] !== $student->nisn) {
            $userData['password'] = Hash::make($data['nisn']);
        }

        $this->userRepository->update($student->user_id, $userData);

        $studentData = collect($data)->except(['name', 'email', 'role'])->toArray();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $studentData['image'] = $this->handleUpload($student->image, $request->file('image'));
        }

        $this->studentRepository->update($student->id, $studentData);

        return $this->studentRepository->show($id);
    }

    public function delete(string $id): bool
    {
        $student = $this->studentRepository->show($id);

        if ($student->image) {
            $this->remove($student->image);
        }

        $this->studentRepository->delete($student->id);
        $this->userRepository->delete($student->user_id);

        return true;
    }

    public function show(string $id): Student
    {
        return $this->studentRepository->show($id);
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->studentRepository->search($request);
    }

    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) {
            $this->remove($oldFile);
        }
        return $this->upload(UploadDiskEnum::STUDENT->value, $file);
    }
}