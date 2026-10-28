<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const PRIORITIES = ['low', 'medium', 'high'];
    public const STATUSES   = ['pending', 'in_progress', 'completed'];

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'description',
        'due_at',
        'priority',
        'status',
        'reminder_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at'       => 'datetime',
            'reminder_at'  => 'datetime',
            'completed_at' => 'datetime',
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

    /**
     * Overdue is a derived property, never stored. Computed from due_at + status.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'completed'
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}