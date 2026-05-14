<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Operator\StoreEmployeeRequest;
use App\Http\Requests\Operator\UpdateEmployeeRequest;
use App\Enums\UploadDiskEnum;
use App\Enums\GenderEnum;
use App\Imports\EmployeeImport;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeeService
{
    use UploadTrait;

    private UserRepository $userRepository;
    private EmployeeRepository $employeeRepository;
    
    public function __construct(UserRepository $userRepository, EmployeeRepository $employeeRepository)
    {
        $this->userRepository = $userRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function store(StoreEmployeeRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $roles = $data['roles'];

            if ($request->hasFile('image')) {
                $data['image'] = $this->upload(
                    UploadDiskEnum::TEACHER->value,
                    $request->file('image')
                );
            } else {
                $data['image'] = ($data['gender'] === GenderEnum::MALE->value) ? 'default_image/teacher-boy.png' : 'default_image/teacher-girl.png';
            }

            $user = $this->userRepository->store([
                'id'       => (string) Str::uuid(),
                'name'     => $data['name'],
                'slug'     => Str::slug($data['name']),
                'email'    => $data['email'],
                'password' => Hash::make($data['nip']),
            ]);

            $user->syncRoles($roles);

            $employee = $this->employeeRepository->store([
                'id'      => (string) Str::uuid(),
                'user_id' => $user->id,
                ...collect($data)->except(['roles', 'name', 'email'])->toArray(),
            ]);

            return $this->employeeRepository->show($employee->id);
        });
    }

    public function update(string $id, UpdateEmployeeRequest $request)
    {
        return DB::transaction(function () use ($id, $request) {
            $employee = $this->employeeRepository->show($id);
            $data = $request->validated();
            $roles = $data['roles'];

            $updateData = [
                'name'  => $data['name'],
                'slug'  => Str::slug($data['name']),
                'email' => $data['email'],
            ];

            $this->userRepository->update($employee->user_id, $updateData);

            $user = $this->userRepository->show($employee->user_id);
            $user->syncRoles($roles);

            if ($request->hasFile('image')) {
                $data['image'] = $this->handleUpload($employee->image, $request->file('image'));
            }

            $this->employeeRepository->update($id, collect($data)->except(['roles', 'name', 'email'])->toArray());

            return $this->employeeRepository->show($id);
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $employee = $this->employeeRepository->show($id);

            if ($employee->image) {
                $this->remove($employee->image);
            }

            $this->employeeRepository->delete($employee->id);
            $this->userRepository->delete($employee->user_id);

            return true;
        });
    }

    public function show(string $id)
    {
        return $this->employeeRepository->show($id);
    }

    public function getWithFilter($request)
    {
        return $this->employeeRepository->search($request);
    }

    private function handleUpload(?string $old, $file)
    {
        if ($old) {
            $this->remove($old);
        }
        return $this->upload(UploadDiskEnum::TEACHER->value, $file);
    }

    public function importEmployees(mixed $file): array
    {

        $storedPath = $file->store('imports/tmp', 'local');
        $fullPath   = storage_path('app/' . $storedPath);

        try {
            $import = new EmployeeImport();
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