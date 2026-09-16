<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ProjectProgressService $progress): Response
    {
        $user = $request->user();
        $taskQuery = Task::forUser($user);
        $stats = [
            'projects' => Project::ownedBy($user)->personal()->count(),
            'tasks' => (clone $taskQuery)->count(),
            'in_progress' => (clone $taskQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $taskQuery)->where('status', 'done')->count(),
        ];
        $featured = Project::ownedBy($user)->personal()->where('status', 'active')->withCount(['tasks', 'tasks as done_tasks_count' => fn (Builder $q) => $q->where('status', 'done')])->orderBy('deadline')->first();
        if ($featured) {
            $featured->setAttribute('progress', $progress->summary($featured));
        }
        $tasks = Task::forUser($user)->with(['project:id,name', 'attachments'])->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END")->orderBy('deadline')->limit(8)->get();
        $upcoming = Task::forUser($user)->with(['project:id,name', 'attachments'])->where('status', '!=', 'done')->orderBy('deadline')->limit(5)->get();
        $insights = $this->insights($user);

        return Inertia::render('Dashboard', compact('stats', 'featured', 'tasks', 'upcoming', 'insights'));
    }

    /** Lightweight analytics derived from the user's tasks in a single query. */
    private function insights($user): array
    {
        $all = Task::forUser($user)->get(['id', 'status', 'priority', 'deadline', 'updated_at']);
        $total = $all->count();
        $done = $all->where('status', 'done')->count();
        $today = today();

        $deadlines7 = collect(range(0, 6))->map(function ($i) use ($all, $today) {
            $day = $today->copy()->addDays($i);

            return [
                'label' => $day->isoFormat('dd'),
                'value' => $all->filter(fn ($t) => $t->status !== 'done' && $t->deadline && $t->deadline->isSameDay($day))->count(),
            ];
        })->values();

        $completedTrend = collect(range(13, 0))->map(function ($i) use ($all, $today) {
            $day = $today->copy()->subDays($i);

            return $all->filter(fn ($t) => $t->status === 'done' && $t->updated_at && $t->updated_at->isSameDay($day))->count();
        })->values();

        return [
            'completion' => $total ? (int) round($done / $total * 100) : 0,
            'done' => $done,
            'total' => $total,
            'byStatus' => [
                ['key' => 'todo', 'label' => 'To-do', 'value' => $all->where('status', 'todo')->count()],
                ['key' => 'in_progress', 'label' => 'In progress', 'value' => $all->where('status', 'in_progress')->count()],
                ['key' => 'done', 'label' => 'Done', 'value' => $done],
            ],
            'deadlines7' => $deadlines7,
            'completedTrend' => $completedTrend,
        ];
    }
}
