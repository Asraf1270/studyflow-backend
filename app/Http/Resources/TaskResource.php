<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'course_id'    => $this->course_id,
            'title'        => $this->title,
            'description'  => $this->description,
            'due_at'       => $this->due_at?->toIso8601String(),
            'priority'     => $this->priority,
            'status'       => $this->status,
            'is_overdue'   => $this->is_overdue,
            'reminder_at'  => $this->reminder_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}