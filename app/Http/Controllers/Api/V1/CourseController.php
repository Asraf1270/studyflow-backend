<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $courses = $request->user()
            ->courses()
            ->when($request->query('semester'), fn ($q, $sem) => $q->where('semester', $sem))
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 15));

        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request): CourseResource
    {
        $course = $request->user()->courses()->create($request->validated());

        return (new CourseResource($course))->response()->setStatusCode(201);
    }

    public function show(Course $course): CourseResource
    {
        $this->authorize('view', $course);

        return new CourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course): CourseResource
    {
        $course->update($request->validated());

        return new CourseResource($course->fresh());
    }

    public function destroy(Course $course): Response
    {
        $this->authorize('delete', $course);
        $course->delete();

        return response()->noContent();
    }
}