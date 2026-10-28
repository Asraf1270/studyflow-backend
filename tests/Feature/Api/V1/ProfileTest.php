<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('returns the current user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonMissingPath('data.password');
});

it('updates name and email', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/me', ['name' => 'New Name', 'email' => 'New@Example.com'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new@example.com');

    expect($user->fresh()->email)->toBe('new@example.com');
});

it('rejects updating email to one already used', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/me', ['email' => 'taken@example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('changes password when current password is correct', function () {
    $user = User::factory()->create(['password' => 'old-password']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/me/password', [
            'current_password'      => 'old-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertNoContent();

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects password change with wrong current password', function () {
    $user = User::factory()->create(['password' => 'old-password']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/me/password', [
            'current_password'      => 'wrong',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('current_password');
});