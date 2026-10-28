<?php

namespace App\Services\Schedule;

use App\Models\ClassSchedule;
use Illuminate\Support\Collection;

class ScheduleConflictService
{
    /**
     * Detect any schedule that overlaps with the candidate on the same day_of_week.
     * Overlap rule: [start, end) intervals intersect.
     * Returns the conflicting schedule, or null if the slot is free.
     */
    public function findConflict(
        int $userId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null,
    ): ?ClassSchedule {
        return ClassSchedule::query()
            ->where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->first();
    }
}