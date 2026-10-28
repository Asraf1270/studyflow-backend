<?php

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamTopic;
use App\Models\User;

it('lists only the current user exams', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Exam::factory()->count(2)->create(['user_id' => $me->id]);
    Exam::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/exams')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates an exam owned by the current user', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/exams', [
            'course_id' => $course->id,
            'title'     => 'Midterm',
            'exam_at'   => now()->addWeek()->toIso8601String(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Midterm');
});

it('rejects an exam for another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $otherCourse = Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->postJson('/api/v1/exams', [
            'course_id' => $otherCourse->id,
            'title'     => 'Hijack',
            'exam_at'   => now()->addWeek()->toIso8601String(),
        ])
        ->assertNotFound();
});

it('includes days_remaining on the resource', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);
    $exam = Exam::factory()->create([
        'user_id'   => $user->id,
        'course_id' => $course->id,
        'exam_at'   => now()->addDays(3)->setTime(9, 0),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/exams/{$exam->id}")
        ->assertOk()
        ->assertJsonPath('data.days_remaining', 3);
});

it('forbids viewing another user exam', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson("/api/v1/exams/{$exam->id}")
        ->assertForbidden();
});

it('forbids deleting another user exam', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->deleteJson("/api/v1/exams/{$exam->id}")
        ->assertForbidden();

    expect(Exam::find($exam->id))->not->toBeNull();
});

it('adds a topic to an owned exam', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/exams/{$exam->id}/topics", ['title' => 'Integration by parts'])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Integration by parts')
        ->assertJsonPath('data.is_completed', false);
});

it('forbids adding a topic to another user exam', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->postJson("/api/v1/exams/{$exam->id}/topics", ['title' => 'X'])
        ->assertForbidden();
});

it('marks a topic complete', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $topic = ExamTopic::factory()->create(['exam_id' => $exam->id]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/exam-topics/{$topic->id}", ['is_completed' => true])
        ->assertOk()
        ->assertJsonPath('data.is_completed', true);
});

it('deletes a topic', function () {
    $user = User::factory()->create();
    $exam = Exam::factory()->create(['user_id' => $user->id]);
    $topic = ExamTopic::factory()->create(['exam_id' => $exam->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/exam-topics/{$topic->id}")
        ->assertNoContent();

    expect(ExamTopic::find($topic->id))->toBeNull();
});