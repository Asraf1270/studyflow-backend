<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\StoreExamTopicRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Requests\Exam\UpdateExamTopicRequest;
use App\Http\Resources\ExamResource;
use App\Http\Resources\ExamTopicResource;
use App\Models\Exam;
use App\Models\ExamTopic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $exams = Exam::query()
            ->where('user_id', $request->user()->id)
            ->when($request->query('course_id'), fn ($q, $c) => $q->where('course_id', (int) $c))
            ->when($request->query('upcoming'), fn ($q) => $q->where('exam_at', '>=', now()))
            ->with('topics')
            ->orderBy('exam_at')
            ->get();

        return ExamResource::collection($exams);
    }

    public function store(StoreExamRequest $request): ExamResource
    {
        $data = $request->validated();
        $request->user()->courses()->findOrFail($data['course_id']);

        $exam = Exam::create([...$data, 'user_id' => $request->user()->id]);

        return (new ExamResource($exam->load('topics')))->response()->setStatusCode(201);
    }

    public function show(Exam $exam): ExamResource
    {
        $this->authorize('view', $exam);
        return new ExamResource($exam->load('topics'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): ExamResource
    {
        $data = $request->validated();

        if (isset($data['course_id'])) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        $exam->update($data);
        return new ExamResource($exam->fresh()->load('topics'));
    }

    public function destroy(Exam $exam): Response
    {
        $this->authorize('delete', $exam);
        $exam->delete();
        return response()->noContent();
    }

    // ---------- Topics ----------

    public function storeTopic(StoreExamTopicRequest $request, Exam $exam): ExamTopicResource
    {
        $this->authorize('update', $exam);

        $topic = $exam->topics()->create($request->validated());

        return (new ExamTopicResource($topic))->response()->setStatusCode(201);
    }

    public function updateTopic(UpdateExamTopicRequest $request, ExamTopic $examTopic): ExamTopicResource
    {
        $examTopic->update($request->validated());
        return new ExamTopicResource($examTopic->fresh());
    }

    public function destroyTopic(ExamTopic $examTopic): Response
    {
        $this->authorize('delete', $examTopic);
        $examTopic->delete();
        return response()->noContent();
    }
}