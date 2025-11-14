<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Contracts\Interfaces\ClassroomInterface;
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
use Throwable;

class ClassroomController extends Controller
{
    private ClassroomService $classroomService;
    private ClassroomInterface $classroomRepository;

    public function __construct(ClassroomService $classroomService, ClassroomInterface $classroomRepository)
    {
        $this->classroomService = $classroomService;
        $this->classroomRepository = $classroomRepository;
    }

    public function index(Request $request)
    {
        try {
            $classrooms = $this->classroomRepository->search($request);

            return ResponseHelper::success(
                ClassroomResource::collection($classrooms),
                'List Data Kelas Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(StoreClassroomRequest $request)
    {
        try {
            $classroom = $this->classroomService->store($request->validated());

            return ResponseHelper::success(
                new ClassroomResource($classroom->load(['major', 'levelClass', 'schoolYear', 'teacher.user'])),
                'Data Kelas Berhasil Dibuat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $classroom = $this->classroomRepository->show($id);

            return ResponseHelper::success(
                new ClassroomDetailResource($classroom),
                'Detail Data Kelas Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function update(UpdateClassroomRequest $request, string $id)
    {
        try {
            $classroom = $this->classroomService->update($id, $request->validated());
            
            return ResponseHelper::success(
                new ClassroomResource($classroom->load(['major', 'levelClass', 'schoolYear', 'teacher.user'])),
                'Data Kelas Berhasil Diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function addStudents(AddStudentToClassroomRequest $request, string $id)
    {
        try {
            $classroom = $this->classroomRepository->show($id);
            $updated = $this->classroomService->addStudents($classroom, $request->student_ids);

            return ResponseHelper::success(
                new ClassroomStudentsResource($updated),
                'Siswa Berhasil Ditambahkan ke Kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function removeStudent(string $id, string $studentId)
    {
        try {
            $classroom = $this->classroomRepository->show($id);
            $updated = $this->classroomService->removeStudent($classroom, $studentId);

            return ResponseHelper::success(
                new ClassroomStudentsResource($updated),
                'Siswa Berhasil Dihapus dari Kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function syncStudents(SyncClassroomStudentsRequest $request, string $id)
    {
        try {
            $classroom = $this->classroomRepository->show($id);
            $updated = $this->classroomService->syncStudents($classroom, $request->student_ids);

            return ResponseHelper::success(
                new ClassroomStudentsResource($updated),
                'Data Siswa Kelas Berhasil Disinkronisasi'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function getStudents(string $id)
    {
        try {
            $classroom = $this->classroomRepository->show($id);
            $students = $this->classroomService->getActiveStudents($classroom);

            return ResponseHelper::success(
                $students,
                'Data Siswa Aktif Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function getAvailableStudents(Request $request, string $id)
    {
        try {
            $classroom = $this->classroomRepository->show($id);
            $search = $request->query('search');
            $limit = $request->query('limit', 10);
            
            $students = $this->classroomService->getAvailableStudents($classroom, $search, $limit);
    
            return ResponseHelper::success(
                AvailableStudentResource::collection($students),
                'Data Siswa yang Tersedia Berhasil Diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }
}