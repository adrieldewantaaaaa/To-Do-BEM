<?php

namespace App\Services;

use App\Models\Project;

class ProjectProgressService
{
    public function calculate(Project $project): int
    {
        $total = (int) ($project->tasks_count ?? $project->tasks()->count());
        if ($total === 0) {
            return 0;
        }
        $done = (int) ($project->done_tasks_count ?? $project->tasks()->where('status', 'done')->count());

        return (int) round(($done / $total) * 100);
    }

    public function summary(Project $project): array
    {
        $total = (int) ($project->tasks_count ?? $project->tasks()->count());
        $done = (int) ($project->done_tasks_count ?? $project->tasks()->where('status', 'done')->count());

        return ['total' => $total, 'completed' => $done, 'percentage' => $total ? (int) round(($done / $total) * 100) : 0];
    }
}
