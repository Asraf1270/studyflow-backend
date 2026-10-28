<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Note::query()->where('user_id', $request->user()->id);

        if ($q = $request->query('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }
        if ($courseId = $request->query('course_id')) {
            $query->where('course_id', (int) $courseId);
        }
        if ($request->has('favorite')) {
            $query->where('is_favorite', $request->boolean('favorite'));
        }

        $notes = $query->orderByDesc('is_favorite')
                       ->orderByDesc('last_edited_at')
                       ->paginate((int) $request->query('per_page', 15));

        return NoteResource::collection($notes);
    }

    public function store(StoreNoteRequest $request): NoteResource
    {
        $data = $request->validated();

        if (isset($data['course_id'])) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        $note = Note::create([...$data, 'user_id' => $request->user()->id]);

        return (new NoteResource($note))->response()->setStatusCode(201);
    }

    public function show(Note $note): NoteResource
    {
        $this->authorize('view', $note);
        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $data = $request->validated();

        if (isset($data['course_id']) && $data['course_id'] !== null) {
            $request->user()->courses()->findOrFail($data['course_id']);
        }

        $note->update($data);
        return new NoteResource($note->fresh());
    }

    public function destroy(Note $note): Response
    {
        $this->authorize('delete', $note);
        $note->delete();
        return response()->noContent();
    }
}