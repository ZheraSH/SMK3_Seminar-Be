<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentApiController extends Controller
{
    private StudentService $service;

    public function __construct(StudentService $service)
    {
        $this->service = $service;
    }

    // ==================== CRUD (semua pakai POST) ====================

    public function handle(Request $request): JsonResponse
    {
        $action = $request->input('action');

        return match ($action) {
            'create' => $this->store(app(StoreStudentRequest::class)),
            'update' => $this->update(app(UpdateStudentRequest::class), (string) $request->input('id')),
            'delete' => $this->destroy((string) $request->input('id')),
            'get'    => $this->show((string) $request->input('id')),
            'all'    => $this->index($request),
            default  => response()->json(['status' => false, 'message' => 'Invalid action'], 400),
        };
    }

    public function index(Request $request): JsonResponse
    {
        $students = $this->service->studentsWithoutClassroom($request);
        return response()->json(['status' => true, 'data' => $students]);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->service->store($request);
        return response()->json([
            'status' => true,
            'message' => 'Siswa berhasil ditambahkan',
            'data' => $student
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $student = $this->service->detailProfile($id);
        return response()->json(['status' => true, 'data' => $student]);
    }

    public function update(UpdateStudentRequest $request, string $id): JsonResponse
    {
        $student = $this->service->detailProfile($id);
        $updated = $this->service->update($student, $request);

        return response()->json([
            'status' => true,
            'message' => 'Siswa berhasil diperbarui',
            'data' => $updated
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $student = $this->service->detailProfile($id);
        $this->service->delete($student);

        return response()->json(['status' => true, 'message' => 'Siswa berhasil dihapus']);
    }


    public function dashboard(string $userId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->dashboard($userId)
        ]);
    }

    public function historyAttendance(string $userId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->historyAttendance($userId)
        ]);
    }

    public function lessonSchedule(string $userId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->lessonSchedule($userId)
        ]);
    }

    public function classStudent(string $userId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->classStudent($userId)
        ]);
    }

    public function detailProfile(string $userId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->detailProfile($userId)
        ]);
    }

    public function topPoints(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->topPoints()
        ]);
    }
}
