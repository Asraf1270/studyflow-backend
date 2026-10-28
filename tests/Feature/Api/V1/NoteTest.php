<?php

use App\Models\Note;
use App\Models\User;

it('lists only the current user notes', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Note::factory()->count(3)->create(['user_id' => $me->id]);
    Note::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson('/api/v1/notes')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates a note and sets last_edited_at', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/notes', ['title' => 'Chapter 1', 'content' => 'Key ideas'])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Chapter 1')
        ->assertJsonPath('data.is_favorite', false);

    $note = Note::where('title', 'Chapter 1')->firstOrFail();
    expect($note->last_edited_at)->not->toBeNull();
});

it('rejects a note for another user course', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $otherCourse = \App\Models\Course::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->postJson('/api/v1/notes', ['title' => 'X', 'course_id' => $otherCourse->id])
        ->assertNotFound();
});

it('searches notes by title', function () {
    $user = User::factory()->create();
    Note::factory()->create(['user_id' => $user->id, 'title' => 'Linear algebra summary']);
    Note::factory()->create(['user_id' => $user->id, 'title' => 'Thermodynamics']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notes?q=algebra')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Linear algebra summary');
});

it('searches notes by content', function () {
    $user = User::factory()->create();
    Note::factory()->create(['user_id' => $user->id, 'title' => 'A', 'content' => 'Eigenvalues are...']);
    Note::factory()->create(['user_id' => $user->id, 'title' => 'B', 'content' => 'Entropy is...']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notes?q=Eigenvalues')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters favorite notes only', function () {
    $user = User::factory()->create();
    Note::factory()->create(['user_id' => $user->id, 'is_favorite' => true]);
    Note::factory()->create(['user_id' => $user->id, 'is_favorite' => false]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notes?favorite=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('forbids viewing another user note', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->getJson("/api/v1/notes/{$note->id}")
        ->assertForbidden();
});

it('forbids updating another user note', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $other->id, 'title' => 'Original']);

    $this->actingAs($me, 'sanctum')
        ->patchJson("/api/v1/notes/{$note->id}", ['title' => 'Hijack'])
        ->assertForbidden();

    expect($note->fresh()->title)->toBe('Original');
});

it('forbids deleting another user note', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me, 'sanctum')
        ->deleteJson("/api/v1/notes/{$note->id}")
        ->assertForbidden();

    expect(Note::find($note->id))->not->toBeNull();
});

it('updates last_edited_at on edit', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create([
        'user_id'        => $user->id,
        'last_edited_at' => now()->subHour(),
    ]);
    $original = $note->last_edited_at;

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/notes/{$note->id}", ['content' => 'Updated'])
        ->assertOk();

    expect($note->fresh()->last_edited_at->greaterThan($original))->toBeTrue();
});

it('deletes own note (soft delete)', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/notes/{$note->id}")
        ->assertNoContent();

    expect(Note::find($note->id))->toBeNull();
    expect(Note::withTrashed()->find($note->id))->not->toBeNull();
});