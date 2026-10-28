<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamTopicFactory extends Factory
{
    protected $model = ExamTopic::class;

    public function definition(): array
    {
        return [
            'exam_id'      => Exam::factory(),
            'title'        => $this->faker->sentence(3),
            'is_completed' => false,
        ];
    }
}