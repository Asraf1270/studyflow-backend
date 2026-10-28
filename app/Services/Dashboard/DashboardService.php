<?php

namespace App\Services\Dashboard;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function build(User $user, string $timezone = 'UTC'): array
    {
        $now = CarbonImmutable::now($timezone);
        $dayOfWeek = $now->dayOfWeekIso;

        $todaysClasses = $user->classSchedules()
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        $pendingTasks = $user->tasks()
            ->where('status', '!=', 'completed')
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        $upcomingExams = $user->exams()
            ->where('exam_at', '>=', $now->utc())
            ->orderBy('exam_at')
            ->limit(5)
            ->get();

        $studyToday = (int) $user->studySessions()
            ->where('started_at', '>=', $now->startOfDay()->utc())
            ->where('started_at', '<', $now->endOfDay()->utc())
            ->sum('duration_seconds');

        $studyWeek = (int) $user->studySessions()
            ->where('started_at', '>=', $now->startOfWeek()->utc())
            ->where('started_at', '<', $now->endOfWeek()->utc())
            ->sum('duration_seconds');

        $tasksCompletedWeek = $user->tasks()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $now->startOfWeek()->utc())
            ->count();

        return [
            'todays_classes'        => $todaysClasses,
            'pending_tasks'         => $pendingTasks,
            'upcoming_exams'        => $upcomingExams,
            'study_seconds_today'   => $studyToday,
            'study_seconds_week'    => $studyWeek,
            'tasks_completed_week'  => $tasksCompletedWeek,
        ];
    }
}