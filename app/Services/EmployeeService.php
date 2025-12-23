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
use Illuminate\Pagination\LengthAwarePaginator;

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
        $roles = $data['roles'] ?? [RoleEnum::TEACHER->value];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->upload(UploadDiskEnum::TEACHER->value, $request->file('image'));
        }

        $userData = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['nip']),
        ];

        $user = $this->user->store($userData);
        $user->syncRoles($roles);

        $employeeData = collect($data)->except(['name', 'email', 'roles'])->toArray();
        $employeeData['id'] = (string) Str::uuid();
        $employeeData['user_id'] = $user->id;

        $employee = $this->employee->store($employeeData);
        return $this->employee->show($employee->id);
    }

    public function update(string $id, UpdateEmployeeRequest $request): Employee
    {
        $employee = $this->employee->show($id);
        $data = $request->validated();
        $roles = $data['roles'] ?? [RoleEnum::TEACHER->value];

        $userData = [
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'],
        ];

        if (isset($data['nip']) && $data['nip'] !== $employee->nip) {
            $userData['password'] = Hash::make($data['nip']);
        }

        $this->user->update($employee->user_id, $userData);
        
        $user = $this->user->show($employee->user_id);
        $user->syncRoles($roles);

        $employeeData = collect($data)->except(['name', 'email', 'roles'])->toArray();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $employeeData['image'] = $this->handleUpload($employee->image, $request->file('image'));
        }

        $this->employee->update($employee->id, $employeeData);

        return $this->employee->show($id);
    }

    public function delete(string $id): bool
    {
        $employee = $this->employee->show($id);

        if ($employee->image) {
            $this->remove($employee->image);
        }

        $this->employee->delete($employee->id);
        $this->user->delete($employee->user_id);

        return true;
    }

    public function show(string $id): Employee
    {
        return $this->employee->show($id);
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->employee->search($request);
    }

    private function handleUpload(?string $oldFile, object $file): string
    {
        if ($oldFile) {
            $this->remove($oldFile);
        }
        return $this->upload(UploadDiskEnum::TEACHER->value, $file);
    }
}