<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'course_id'   => null,
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'due_at'      => $this->faker->optional(0.8)->dateTimeBetween('now', '+30 days'),
            'priority'    => $this->faker->randomElement(Task::PRIORITIES),
            'status'      => 'pending',
        ];
    }
}