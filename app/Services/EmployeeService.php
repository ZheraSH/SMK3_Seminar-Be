<?php

namespace App\Services;

use App\Contracts\Interfaces\EmployeeInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Enums\RoleEnum;
use App\Enums\UploadDiskEnum;
use App\Models\Employee;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class EmployeeService
{
    use UploadTrait;

    private UserInterface $user;
    private EmployeeInterface $employee;
    public function __construct(UserInterface $user, EmployeeInterface $employee)
    {
        $this->user = $user;
        $this->employee = $employee;
    }

    public function store(StoreEmployeeRequest $request): Employee
    {
        $data = $request->validated();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->upload(UploadDiskEnum::TEACHER->value, $request->file('image'));
        }

        $userData = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['NIP']),
        ];

        $user = $this->user->store($userData);
        $user->assignRole(RoleEnum::TEACHER->value);

        $employeeData = collect($data)->except(['name','email'])->toArray();
        $employeeData['id'] = (string) Str::uuid();
        $employeeData['user_id'] = $user->id;

        return $this->employee->store($employeeData);
    }

    public function update(Employee $employee, UpdateEmployeeRequest $request): ?Employee
    {
        if (!$employee) return null;

        $data = $request->validated();

        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['NIP']),
        ];

        $employeeData = collect($data)->except(['name','email','role'])->toArray();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $employeeData['image'] = $this->handleUpload($employee->image, $request->file('image'));
        }
        
        $this->user->update($employee->user_id, $userData);
        $this->employee->update($employee->id, $employeeData);

        return $employee->fresh(['user','religion']);
    }

    public function delete(Employee $employee): bool
    {
        if (!$employee) return false;

        if ($employee->image) $this->remove($employee->image);

        $this->employee->delete($employee->id);
        $this->user->delete($employee->user_id);

        return true;
    }

    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) $this->remove($oldFile);
        return $this->upload(UploadDiskEnum::TEACHER->value, $file);
    }
    public function getWithFilter(Request $request, int $pagination = 8): mixed
    {
        return $this->employee->search($request, $pagination);
    }
}