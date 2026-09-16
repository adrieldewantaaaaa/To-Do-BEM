<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;

class TaskAttachmentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $task->project->accessibleBy($user);
    }

    public function view(User $user, TaskAttachment $attachment): bool
    {
        return $attachment->task->project->accessibleBy($user);
    }

    public function delete(User $user, TaskAttachment $attachment): bool
    {
        return $attachment->task->project->accessibleBy($user);
    }
}
