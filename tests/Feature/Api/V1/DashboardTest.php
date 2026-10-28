<?php

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Task;
use App\Models\User;

it('returns empty dashboard for a new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.todays_classes', [])
        ->assertJsonPath('data.pending_tasks', [])
        ->assertJsonPath('data.upcoming_exams', [])
        ->assertJsonPath('data.study_seconds_today', 0)
        ->assertJsonPath('data.study_seconds_week', 0)
        ->assertJsonPath('data.tasks_completed_week', 0);
});

it('returns today classes', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);
    ClassSchedule::factory()->create([
        'user_id'     => $user->id,
        'course_id'   => $course->id,
        'day_of_week' => now()->dayOfWeekIso,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.todays_classes');
});

it('counts completed tasks within the current week', function () {
    $user = User::factory()->create();
    Task::factory()->create([
        'user_id'      => $user->id,
        'status'       => 'completed',
        'completed_at' => now()->startOfWeek()->addHour(),
    ]);
    Task::factory()->create([
        'user_id'      => $user->id,
        'status'       => 'completed',
        'completed_at' => now()->subWeeks(2),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.tasks_completed_week', 1);
});