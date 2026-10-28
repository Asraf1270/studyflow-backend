<?php

namespace App\Policies;

use App\Models\ClassSchedule;
use App\Models\User;

class ClassSchedulePolicy
{
    public function view(User $user, ClassSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }

    public function update(User $user, ClassSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }

    public function delete(User $user, ClassSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }
}