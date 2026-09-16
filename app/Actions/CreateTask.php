<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateTask
{
    public function __construct(private AttachmentService $attachments) {}

    public function execute(Project $project, array $data, array $files = []): Task
    {
        return DB::transaction(function () use ($project, $data, $files) {
            $position = $project->tasks()->where('status', $data['status'])->max('position');
            $task = $project->tasks()->create([...Arr::except($data, ['attachments', 'assignees']), 'position' => is_null($position) ? 0 : $position + 1]);

            // Set created_by explicitly (not mass-assigned)
            $task->created_by = auth()->id();
            $task->save();

            // Sync assignees (only for room projects)
            $assigneeIds = $data['assignees'] ?? [];
            if (! empty($assigneeIds) && $project->room_id) {
                $task->assignees()->sync($assigneeIds);
            }

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $this->attachments->store($task, $file);
                }
            }

            return $task;
        });
    }
}
