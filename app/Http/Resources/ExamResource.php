<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'course_id'      => $this->course_id,
            'title'          => $this->title,
            'exam_at'        => $this->exam_at?->toIso8601String(),
            'location'       => $this->location,
            'notes'          => $this->notes,
            'reminder_at'    => $this->reminder_at?->toIso8601String(),
            'days_remaining' => $this->days_remaining,
            'topics'         => ExamTopicResource::collection($this->whenLoaded('topics')),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}