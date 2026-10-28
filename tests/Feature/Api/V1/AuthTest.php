<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

// ---------- Register ----------

it('registers a user and returns a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Alice',
        'email'                 => 'Alice@Example.com',
        'password'              => 'correct-horse-battery',
        'password_confirmation' => 'correct-horse-battery',
        'device_name'           => 'pixel-7',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user.email', 'alice@example.com')
        ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

    $user = User::where('email', 'alice@example.com')->firstOrFail();
    expect(Hash::check('correct-horse-battery', $user->password))->toBeTrue();
    expect(PersonalAccessToken::where('tokenable_id', $user->id)->count())->toBe(1);
    expect($response->json('data.user'))->not->toHaveKey('password');
});

it('rejects registration with a duplicate email', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Dup',
        'email'                 => 'dup@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects registration when passwords do not match', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Mismatch',
        'email'                 => 'mm@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'different',
    ])->assertStatus(422)->assertJsonValidationErrors('password');
});

// ---------- Login ----------

it('logs in with correct credentials (case-insensitive email)', function () {
    User::factory()->create([
        'email'    => 'bob@example.com',
        'password' => Hash::make('secret-123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'       => 'BOB@example.com',
        'password'    => 'secret-123',
        'device_name' => 'iphone-15',
    ])->assertOk()
      ->assertJsonStructure(['data' => ['user', 'token']]);
});

it('rejects login with wrong password using 422', function () {
    User::factory()->create([
        'email'    => 'carol@example.com',
        'password' => Hash::make('right-password'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'       => 'carol@example.com',
        'password'    => 'wrong-password',
        'device_name' => 'pixel-7',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects login when the email does not exist', function () {
    $this->postJson('/api/v1/auth/login', [
        'email'       => 'nobody@example.com',
        'password'    => 'whatever',
        'device_name' => 'pixel-7',
    ])->assertStatus(422);
});

// ---------- Logout ----------

it('logs out and revokes only the current token', function () {
    $user = User::factory()->create();
    $otherToken = $user->createToken('other-device')->plainTextToken;
    $current    = $user->createToken('pixel-7');
    $currentToken = $current->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$currentToken}")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect(PersonalAccessToken::find($current->accessToken->id))->toBeNull();
    expect(PersonalAccessToken::where('name', 'other-device')->count())->toBe(1);

    // Old token is now unusable
    $this->withHeader('Authorization', "Bearer {$currentToken}")
        ->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();

    // Other device still works
    $this->withHeader('Authorization', "Bearer {$otherToken}")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

it('requires auth to log out', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});

// ---------- Rate limiting ----------

it('rate limits registration attempts per IP', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/v1/auth/register', [])->assertStatus(422);
    }
    $this->postJson('/api/v1/auth/register', [])->assertStatus(429);
});