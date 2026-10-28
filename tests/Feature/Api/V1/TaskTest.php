<?php

use App\Models\Course;
use App\Models\Task;
use App\Models\User;

it('lists only the current user tasks', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Task::factory()->count(2)->create(['user_id' => $me->id]);
    Task::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/tasks')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a task with defaults', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tasks', ['title' => 'Read chapter 3'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.priority', 'medium');
});

it('rejects a task for another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $otherCourse = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->postJson('/api/v1/tasks', ['title' => 'X', 'course_id' => $otherCourse->id])
        ->assertNotFound();
});

it('marks is_overdue for past due pending tasks', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'status'  => 'pending',
        'due_at'  => now()->subDay(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/tasks/{$task->id}")
        ->assertOk()
        ->assertJsonPath('data.is_overdue', true);
});

it('does not mark completed past due tasks as overdue', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id'      => $user->id,
        'status'       => 'completed',
        'due_at'       => now()->subDay(),
        'completed_at' => now()->subDay(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/tasks/{$task->id}")
        ->assertOk()
        ->assertJsonPath('data.is_overdue', false);
});

it('marks completed_at when transitioning to completed', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'completed'])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    expect($task->fresh()->completed_at)->not->toBeNull();
});

it('clears completed_at when moving back from completed', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id'      => $user->id,
        'status'       => 'completed',
        'completed_at' => now(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => 'pending'])
        ->assertOk();

    expect($task->fresh()->completed_at)->toBeNull();
});

it('filters overdue tasks only', function () {
    $user = User::factory()->create();
    Task::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'due_at' => now()->subDay()]);
    Task::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'due_at' => now()->addDay()]);
    Task::factory()->create(['user_id' => $user->id, 'status' => 'completed', 'due_at' => now()->subDay()]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/tasks?overdue=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('forbids updating another user task', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->patchJson("/api/v1/tasks/{$task->id}", ['title' => 'Hijack'])
        ->assertForbidden();
});