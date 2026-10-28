<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'day_of_week',
        'start_time',
        'end_time',
        'location',
        'instructor',
        'reminder_offset_minutes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week'             => 'integer',
            'reminder_offset_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}