<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassScheduleResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\TaskResource;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $timezone = $request->query('timezone', 'UTC');
        $data = $this->dashboard->build($request->user(), $timezone);

        return response()->json([
            'data' => [
                'todays_classes'       => ClassScheduleResource::collection($data['todays_classes']),
                'pending_tasks'        => TaskResource::collection($data['pending_tasks']),
                'upcoming_exams' => ExamResource::collection($data['upcoming_exams']),
                'study_seconds_today'  => $data['study_seconds_today'],
                'study_seconds_week'   => $data['study_seconds_week'],
                'tasks_completed_week' => $data['tasks_completed_week'],
            ],
        ]);
    }
}