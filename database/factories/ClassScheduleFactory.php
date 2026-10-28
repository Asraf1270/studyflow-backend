<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    protected $model = ClassSchedule::class;

    public function definition(): array
    {
        $start = $this->faker->randomElement(['08:00', '10:00', '13:00', '15:00']);
        $end   = date('H:i', strtotime($start) + 3600);

        return [
            'course_id'               => Course::factory(),
            'day_of_week'             => $this->faker->numberBetween(1, 7),
            'start_time'              => $start,
            'end_time'                => $end,
            'location'                => 'Room ' . $this->faker->numberBetween(100, 500),
            'instructor'              => $this->faker->name(),
            'reminder_offset_minutes' => null,
        ];
    }
}