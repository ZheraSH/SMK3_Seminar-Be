<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Requests\AddStudentToClassroomRequest;
use App\Http\Requests\SyncClassroomStudentsRequest;
use App\Http\Resources\ClassroomResource;
use App\Http\Resources\ClassroomDetailResource;
use App\Services\ClassroomService;
use App\Helpers\ResponseHelper;
use App\Http\Resources\AvailableStudentResource;
use App\Http\Resources\ClassroomStudentsResource;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    private ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->classroomService->search($request);

            if ($request->has('page')) {
                return ResponseHelper::pagination($data, 'List data kelas berhasil diambil');
            }

            return ResponseHelper::success(
                ClassroomResource::collection($data),
                'List data kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
    
    public function store(StoreClassroomRequest $request)
    {
        try {
            $data = $this->classroomService->store($request->validated());
            
            return ResponseHelper::success(
                new ClassroomResource($data->load(['major', 'levelClass', 'schoolYear', 'teacher.user'])),
                'Data kelas berhasil dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->classroomService->show($id);

            return ResponseHelper::success(
                new ClassroomDetailResource($data),
                'Detail data kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data kelas tidak ditemukan');
        }
    }

    public function update(UpdateClassroomRequest $request, string $id)
    {
        try {
            $data = $this->classroomService->update($id, $request->validated());
            
            return ResponseHelper::success(
                new ClassroomResource($data->load(['major', 'levelClass', 'schoolYear', 'teacher.user'])),
                'Data kelas berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function addStudents(AddStudentToClassroomRequest $request, string $id)
    {
        try {
            $classroom = $this->classroomService->show($id);
            $data = $this->classroomService->addStudents($classroom, $request->student_ids);

            return ResponseHelper::success(
                new ClassroomStudentsResource($data),
                'Siswa berhasil ditambahkan ke kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function removeStudent(string $id, string $studentId)
    {
        try {
            $classroom = $this->classroomService->show($id);
            $data = $this->classroomService->removeStudent($classroom, $studentId);

            return ResponseHelper::success(
                new ClassroomStudentsResource($data),
                'Siswa berhasil dihapus dari kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function syncStudents(SyncClassroomStudentsRequest $request, string $id)
    {
        try {
            $classroom = $this->classroomService->show($id);
            $data = $this->classroomService->syncStudents($classroom, $request->student_ids);

            return ResponseHelper::success(
                new ClassroomStudentsResource($data),
                'Data siswa kelas berhasil disinkronisasi'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function getStudents(string $id)
    {
        try {
            $classroom = $this->classroomService->show($id);
            $data = $this->classroomService->getActiveStudents($classroom);

            return ResponseHelper::success(
                $data,
                'Data siswa aktif berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getAvailableStudents(Request $request, string $id)
    {
        try {
            $classroom = $this->classroomService->show($id);
            $search = $request->query('search');
            $limit = $request->query('limit', 10);
            
            $data = $this->classroomService->getAvailableStudents($classroom, $search, $limit);
    
            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa yang tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
}