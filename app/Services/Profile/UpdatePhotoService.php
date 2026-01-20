<?php

namespace App\Services\Profile;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Http\Requests\Profile\UpdatePhotoRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePhotoService
{
    private StudentRepository $studentRepository;
    private EmployeeRepository $employeeRepository;

    public function __construct(StudentRepository $studentRepository, EmployeeRepository $employeeRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function execute(UpdatePhotoRequest $request): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('User tidak terautentikasi', 401);
        }

        $student = $user->student;
        $employee = $user->employee;

        if (!$student && !$employee) {
            throw new \Exception('Profil tidak ditemukan', 404);
        }

        $photo = $request->file('photo');
        $photoPath = $photo->store('photos', 'public');

        if ($student) {
            $this->studentRepository->updateImage($student->id, $photoPath);
        }
        if ($employee) {
            $this->employeeRepository->updateImage($employee->id, $photoPath);
        }
    }
}
