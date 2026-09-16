<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskPositionService
{
    public function move(Task $task, string $status, int $position): Task
    {
        return DB::transaction(function () use ($task, $status, $position) {
            $oldStatus = $task->status;
            $oldPosition = $task->position;
            $targetCount = Task::where('project_id', $task->project_id)->where('status', $status)->where('id', '!=', $task->id)->count();
            $position = min($position, $targetCount);
            if ($oldStatus === $status) {
                $query = Task::where('project_id', $task->project_id)->where('status', $status)->where('id', '!=', $task->id);
                if ($position > $oldPosition) {
                    $query->whereBetween('position', [$oldPosition + 1, $position])->decrement('position');
                }
                if ($position < $oldPosition) {
                    $query->whereBetween('position', [$position, $oldPosition - 1])->increment('position');
                }
            } else {
                Task::where('project_id', $task->project_id)->where('status', $oldStatus)->where('position', '>', $oldPosition)->decrement('position');
                Task::where('project_id', $task->project_id)->where('status', $status)->where('position', '>=', $position)->increment('position');
            }
            $task->update(['status' => $status, 'position' => $position]);

            return $task->refresh();
        });
    }
}
