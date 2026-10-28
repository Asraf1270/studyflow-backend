<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'course_id'               => $this->course_id,
            'day_of_week'             => $this->day_of_week,
            'start_time'              => substr((string) $this->start_time, 0, 5),
            'end_time'                => substr((string) $this->end_time, 0, 5),
            'location'                => $this->location,
            'instructor'              => $this->instructor,
            'reminder_offset_minutes' => $this->reminder_offset_minutes,
        ];
    }
}