<?php

namespace App\Http\Controllers\Api\Operator;

use App\Services\Operator\ClassroomStudentsService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\ClassroomStudentsResource;
use App\Http\Resources\Operator\AvailableStudentResource;
use App\Http\Resources\Operator\PromotionResource;
use App\Http\Requests\Operator\AddStudentToClassroomRequest;
use App\Http\Requests\Operator\ImportClassroomStudentRequest;
use App\Http\Requests\Operator\PromoteClassRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class ClassroomStudentsController extends Controller
{
    private ClassroomStudentsService $classroomStudentsService;

    public function __construct(ClassroomStudentsService $classroomStudentsService)
    {
        $this->classroomStudentsService = $classroomStudentsService;
    }

    public function index(Request $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getByClassroom($classroomId, $request);

            return ResponseHelper::pagination(
                $data,
                ClassroomStudentsResource::class,
                'List data siswa kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getAvailableStudents(Request $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->getAvailableStudents($classroomId, $request);

            return ResponseHelper::success(
                AvailableStudentResource::collection($data),
                'Data siswa tersedia berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function store(AddStudentToClassroomRequest $request, string $classroomId)
    {
        try {
            $data = $this->classroomStudentsService->addStudents($classroomId, $request->student_ids);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data),
                'Siswa berhasil ditambahkan ke kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function destroy(string $classroomId, string $studentId)
    {
        try {
            $data = $this->classroomStudentsService->removeStudent($classroomId, $studentId);

            return ResponseHelper::success(
                ClassroomStudentsResource::collection($data),
                'Siswa berhasil dihapus dari kelas'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function promote(PromoteClassRequest $request, string $classroomId)
    {
        try {
            $result = $this->classroomStudentsService->promoteClass(
                $classroomId,
                $request->validated('homeroom_teacher_id')
            );

            return ResponseHelper::success(
                new PromotionResource($result),
                "Berhasil menaikkan {$result['students_promoted']} siswa dari {$result['from_level']} ke {$result['to_level']}."
            );

        } catch (UniqueConstraintViolationException) {
            return ResponseHelper::error('Siswa sudah pernah dinaikkan ke tahun ajaran ini. Tidak ada perubahan dilakukan.', 409);
        } catch (\RuntimeException $e) {
            return ResponseHelper::error($e->getMessage(), $e->getCode() === 422 ? 422 : 400);
        } catch (\Throwable $th) {
            return ResponseHelper::error('Terjadi kesalahan saat memproses kenaikan kelas: ' . $th->getMessage(), 500);
        }
    }

    public function import(ImportClassroomStudentRequest $request, string $classroomId)
    {
        try {
            $result = $this->classroomStudentsService->importStudents($classroomId, $request->file('file'));

            return ResponseHelper::success(
                [
                    'imported_count' => $result['imported_count'],
                    'error_count'    => count($result['errors']),
                    'errors'         => $result['errors'],
                ],
                "Berhasil mengimport {$result['imported_count']} siswa ke kelas.",
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
