<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Room;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Policies\ProjectPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TaskAttachmentPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Room::class => RoomPolicy::class,
        Task::class => TaskPolicy::class,
        TaskAttachment::class => TaskAttachmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
