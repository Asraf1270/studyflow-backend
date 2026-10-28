<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\ClassScheduleResource;
use App\Models\ClassSchedule;
use App\Services\Schedule\ScheduleConflictService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleConflictService $conflicts)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $schedules = ClassSchedule::query()
            ->where('user_id', $request->user()->id)
            ->when($request->query('day_of_week'), fn ($q, $d) => $q->where('day_of_week', (int) $d))
            ->when($request->query('course_id'), fn ($q, $c) => $q->where('course_id', (int) $c))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return ClassScheduleResource::collection($schedules);
    }

    public function store(StoreScheduleRequest $request): ClassScheduleResource
    {
        $data = $request->validated();

        // Ensure the course belongs to the current user (prevents cross-user course_id injection)
        $course = $request->user()->courses()->findOrFail($data['course_id']);

        $conflict = $this->conflicts->findConflict(
            $request->user()->id,
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
        );

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => ["This schedule conflicts with an existing class ({$conflict->start_time}–{$conflict->end_time})."],
            ]);
        }

        $schedule = ClassSchedule::create([
            ...$data,
            'user_id'   => $request->user()->id,
            'course_id' => $course->id,
        ]);

        return (new ClassScheduleResource($schedule))->response()->setStatusCode(201);
    }

    public function update(UpdateScheduleRequest $request, ClassSchedule $schedule): ClassScheduleResource
    {
        $data = $request->validated();

        if (isset($data['course_id'])) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        // Merge for conflict check — a partial update still needs to be validated against the full slot
        $dayOfWeek = $data['day_of_week']   ?? $schedule->day_of_week;
        $start     = $data['start_time']    ?? substr((string) $schedule->start_time, 0, 5);
        $end       = $data['end_time']      ?? substr((string) $schedule->end_time, 0, 5);

        $conflict = $this->conflicts->findConflict(
            $request->user()->id,
            (int) $dayOfWeek,
            $start,
            $end,
            ignoreId: $schedule->id,
        );

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => ["This schedule conflicts with an existing class ({$conflict->start_time}–{$conflict->end_time})."],
            ]);
        }

        $schedule->update($data);

        return new ClassScheduleResource($schedule->fresh());
    }

    public function destroy(ClassSchedule $schedule): Response
    {
        $this->authorize('delete', $schedule);
        $schedule->delete();

        return response()->noContent();
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        $dayOfWeek = (int) now()->dayOfWeekIso; // 1=Mon..7=Sun

        $schedules = ClassSchedule::query()
            ->where('user_id', $request->user()->id)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        return ClassScheduleResource::collection($schedules);
    }
}