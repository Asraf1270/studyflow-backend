<?php

use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\User;

it('lists only the current user schedules', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    ClassSchedule::factory()->count(2)->create(['user_id' => $me->id]);
    ClassSchedule::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/schedules')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a schedule on an empty slot', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/schedules', [
            'course_id'   => $course->id,
            'day_of_week' => 1,
            'start_time'  => '09:00',
            'end_time'    => '10:00',
        ])
        ->assertCreated()
        ->assertJsonPath('data.day_of_week', 1);
});

it('rejects a schedule that overlaps an existing class', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);
    ClassSchedule::factory()->create([
        'user_id'     => $user->id,
        'course_id'   => $course->id,
        'day_of_week' => 1,
        'start_time'  => '09:00',
        'end_time'    => '10:00',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/schedules', [
            'course_id'   => $course->id,
            'day_of_week' => 1,
            'start_time'  => '09:30',
            'end_time'    => '10:30',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('start_time');
});

it('allows back-to-back classes that touch but do not overlap', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);
    ClassSchedule::factory()->create([
        'user_id'     => $user->id,
        'course_id'   => $course->id,
        'day_of_week' => 1,
        'start_time'  => '09:00',
        'end_time'    => '10:00',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/schedules', [
            'course_id'   => $course->id,
            'day_of_week' => 1,
            'start_time'  => '10:00',
            'end_time'    => '11:00',
        ])
        ->assertCreated();
});

it('rejects a schedule for another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $otherCourse = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->postJson('/api/v1/schedules', [
            'course_id'   => $otherCourse->id,
            'day_of_week' => 2,
            'start_time'  => '09:00',
            'end_time'    => '10:00',
        ])
        ->assertNotFound();
});

it('forbids updating another user schedule', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $schedule = ClassSchedule::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->patchJson("/api/v1/schedules/{$schedule->id}", ['start_time' => '08:00'])
        ->assertForbidden();
});

it('rejects update that would create a conflict', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);
    ClassSchedule::factory()->create([
        'user_id'     => $user->id,
        'course_id'   => $course->id,
        'day_of_week' => 1,
        'start_time'  => '09:00',
        'end_time'    => '10:00',
    ]);
    $target = ClassSchedule::factory()->create([
        'user_id'     => $user->id,
        'course_id'   => $course->id,
        'day_of_week' => 1,
        'start_time'  => '14:00',
        'end_time'    => '15:00',
    ]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/schedules/{$target->id}", [
            'start_time' => '09:30',
            'end_time'   => '10:30',
        ])
        ->assertStatus(422);
});

it('deletes own schedule', function () {
    $user = User::factory()->create();
    $schedule = ClassSchedule::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/schedules/{$schedule->id}")
        ->assertNoContent();

    expect(ClassSchedule::find($schedule->id))->toBeNull();
});