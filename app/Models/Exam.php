<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'exam_at',
        'location',
        'notes',
        'reminder_at',
    ];

    protected function casts(): array
    {
        return [
            'exam_at'     => 'datetime',
            'reminder_at' => 'datetime',
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

    public function topics(): HasMany
    {
        return $this->hasMany(ExamTopic::class);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->exam_at === null) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->exam_at->copy()->startOfDay(), false);
    }
}