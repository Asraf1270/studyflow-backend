<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'course_id' => Course::factory(),
            'title'     => $this->faker->sentence(3),
            'exam_at'   => $this->faker->dateTimeBetween('now', '+60 days'),
            'location'  => 'Hall ' . $this->faker->numberBetween(1, 5),
            'notes'     => null,
        ];
    }
}