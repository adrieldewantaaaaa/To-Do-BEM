<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Tasks are collaborative: any member of the project's room (or the personal owner) may act on them.
    public function view(User $user, Task $task): bool
    {
        return $task->project->accessibleBy($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->accessibleBy($user);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->project->accessibleBy($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->accessibleBy($user);
    }
}
