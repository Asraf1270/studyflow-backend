<?php

namespace App\Policies;

use App\Models\ExamTopic;
use App\Models\User;

class ExamTopicPolicy
{
    public function update(User $user, ExamTopic $topic): bool
    {
        return $user->id === $topic->exam->user_id;
    }

    public function delete(User $user, ExamTopic $topic): bool
    {
        return $user->id === $topic->exam->user_id;
    }
}