<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'course_id'      => null,
            'title'          => $this->faker->sentence(4),
            'content'        => $this->faker->paragraph(),
            'is_favorite'    => false,
            'last_edited_at' => now(),
        ];
    }
}