<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function __invoke(Request $request, ProjectProgressService $service): Response
    {
        $tasks = Task::forUser($request->user());
        $totalTasks = (clone $tasks)->count();
        $completed = (clone $tasks)->where('status', 'done')->count();
        $summary = ['projects' => Project::ownedBy($request->user())->count(), 'tasks' => $totalTasks, 'completed' => $completed, 'completion_rate' => $totalTasks ? (int) round($completed / $totalTasks * 100) : 0, 'overdue' => (clone $tasks)->where('status', '!=', 'done')->whereDate('deadline', '<', today())->count()];
        $byStatus = ['todo' => (clone $tasks)->where('status', 'todo')->count(), 'in_progress' => (clone $tasks)->where('status', 'in_progress')->count(), 'done' => $completed];
        $projects = Project::ownedBy($request->user())->withCount(['tasks', 'tasks as done_tasks_count' => fn (Builder $q) => $q->where('status', 'done')])->orderBy('deadline')->limit(8)->get()->map(function ($p) use ($service) {
            $p->setAttribute('progress', $service->summary($p));

            return $p;
        });

        return Inertia::render('Reports/Index', compact('summary', 'byStatus', 'projects'));
    }
}
