<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'name'     => $this->faker->words(3, true),
            'code'     => strtoupper($this->faker->bothify('???-###')),
            'semester' => 'Fall 2026',
            'color'    => '#4F46E5',
        ];
    }
}