<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\LessonHourRepository;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LessonHour\StoreLessonHourRequest;
use App\Http\Requests\LessonHour\UpdateLessonHourRequest;
use App\Http\Resources\LessonHourResource;

class LessonHourController extends Controller
{
    protected LessonHourRepository $lessonHour;

    public function __construct(LessonHourRepository $lessonHour)
    {
        $this->lessonHour = $lessonHour;
    }

    public function index()
    {
        $data = $this->lessonHour->paginate(10);
        return ResponseHelper::success(LessonHourResource::collection($data), 'Lesson hours retrieved successfully');
    }

    public function store(StoreLessonHourRequest $request)
    {
        $data = $this->lessonHour->store($request->validated());
        return ResponseHelper::success(new LessonHourResource($data), 'Lesson hour created successfully');
    }

    public function show($id)
    {
        $data = $this->lessonHour->show($id);
        return ResponseHelper::success(new LessonHourResource($data), 'Lesson hour detail retrieved');
    }

    public function update(UpdateLessonHourRequest $request, $id)
    {
        $data = $this->lessonHour->update($id, $request->validated());
        return ResponseHelper::success(new LessonHourResource($data), 'Lesson hour updated successfully');
    }

    public function destroy($id)
    {
        $this->lessonHour->delete($id);
        return ResponseHelper::success(null, 'Lesson hour deleted successfully');
    }
}
