<?php

use App\Models\Course;
use App\Models\User;

it('lists only the current user courses', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Course::factory()->count(2)->create(['user_id' => $me->id]);
    Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/courses')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a course owned by the current user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/courses', [
            'name'     => 'Numerical Analysis',
            'code'     => 'MATH-301',
            'semester' => 'Fall 2026',
            'color'    => '#4F46E5',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Numerical Analysis');

    $course = Course::findOrFail($response->json('data.id'));
    expect($course->user_id)->toBe($user->id);
});

it('rejects invalid color', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/courses', ['name' => 'X', 'color' => 'red'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('color');
});

it('shows a course owned by the current user', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/courses/{$course->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $course->id);
});

it('forbids viewing another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson("/api/v1/courses/{$course->id}")
        ->assertForbidden();
});

it('forbids updating another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->patchJson("/api/v1/courses/{$course->id}", ['name' => 'Hijacked'])
        ->assertForbidden();

    expect($course->fresh()->name)->not->toBe('Hijacked');
});

it('forbids deleting another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->deleteJson("/api/v1/courses/{$course->id}")
        ->assertForbidden();

    expect(Course::find($course->id))->not->toBeNull();
});

it('deletes own course', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/courses/{$course->id}")
        ->assertNoContent();

    expect(Course::find($course->id))->toBeNull();
});

it('returns 404 for a course that does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/courses/999999')
        ->assertNotFound();
});