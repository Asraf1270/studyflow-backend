<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'title', 'exam_at', 'location', 'notes', 'reminder_at'];

    protected function casts(): array
    {
        return [
            'exam_at'     => 'datetime',
            'reminder_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}