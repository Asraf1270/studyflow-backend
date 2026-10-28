<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Task::query()->where('user_id', $request->user()->id);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($courseId = $request->query('course_id')) {
            $query->where('course_id', (int) $courseId);
        }
        if ($request->boolean('overdue')) {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_at')
                  ->where('due_at', '<', now());
        }
        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->orderByRaw('completed_at IS NULL DESC')
                       ->orderBy('due_at')
                       ->paginate((int) $request->query('per_page', 15));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $data = $request->validated();

        if (isset($data['course_id'])) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        $data['user_id'] = $request->user()->id;

        $task = Task::create($data);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $data = $request->validated();

        if (isset($data['course_id']) && $data['course_id'] !== null) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        if (isset($data['status'])) {
            $data['completed_at'] = $data['status'] === 'completed' ? now() : null;
        }

        $task->update($data);

        return new TaskResource($task->fresh());
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource
    {
        $status = $request->validated('status');

        $task->update([
            'status'       => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);

        return new TaskResource($task->fresh());
    }

    public function destroy(Task $task): Response
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->noContent();
    }
}