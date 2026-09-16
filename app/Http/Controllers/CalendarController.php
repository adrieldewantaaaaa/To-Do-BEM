<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $projects = Project::ownedBy($request->user())->get(['id', 'name', 'deadline', 'status'])->map(fn ($p) => ['id' => 'project-'.$p->id, 'type' => 'project', 'title' => $p->name, 'date' => $p->deadline->toDateString(), 'status' => $p->status, 'url' => route('projects.show', $p)]);
        $tasks = Task::forUser($request->user())->with('project:id,name')->get()->map(fn ($t) => ['id' => 'task-'.$t->id, 'type' => 'task', 'title' => $t->title, 'project' => $t->project->name, 'date' => $t->deadline->toDateString(), 'status' => $t->status, 'priority' => $t->priority, 'project_url' => route('projects.show', $t->project)]);

        return Inertia::render('Calendar/Index', ['events' => $projects->concat($tasks)->values()]);
    }
}
