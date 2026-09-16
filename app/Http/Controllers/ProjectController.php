<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, ProjectProgressService $progress): Response
    {
        $this->authorize('viewAny', Project::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'in:all,active,completed,archived'], 'sort' => ['nullable', 'in:latest,oldest,deadline,progress'], 'view' => ['nullable', 'in:grid,list']]);
        $query = Project::ownedBy($request->user())->personal()->withCount(['tasks', 'tasks as done_tasks_count' => fn (Builder $q) => $q->where('status', 'done')])
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $q) => $q->where('status', $filters['status']));
        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->oldest(),
            'deadline' => $query->orderBy('deadline'),
            'progress' => $query->orderByRaw('CASE WHEN tasks_count = 0 THEN 0 ELSE (done_tasks_count * 100.0 / tasks_count) END DESC'),
            default => $query->latest(),
        };
        $projects = $query->paginate(12)->withQueryString();
        $projects->getCollection()->transform(function (Project $project) use ($progress) {
            $project->setAttribute('progress', $progress->summary($project));

            return $project;
        });

        return Inertia::render('Projects/Index', ['projects' => $projects, 'filters' => $filters]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Projects/Create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);
        $project = $request->user()->projects()->create($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Project $project, ProjectProgressService $progress): Response
    {
        $this->authorize('view', $project);
        $project->load([
            'tasks' => fn ($q) => $q->with(['attachments', 'assignees:id,name', 'creator:id,name'])->orderBy('status')->orderBy('position'),
            'room:id,name',
        ]);
        $project->setAttribute('progress', $progress->summary($project));

        // Provide room members for the assignee picker (room projects only)
        $roomMembers = [];
        if ($project->room_id) {
            $roomMembers = $project->room->members()->select('users.id', 'users.name')->get()->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
            ]);
        }

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'canManage' => $project->manageableBy(auth()->user()),
            'roomMembers' => $roomMembers,
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', ['project' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->update($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        foreach ($project->tasks()->pluck('id') as $taskId) {
            Storage::disk('local')->deleteDirectory('attachments/'.$project->user_id.'/'.$taskId);
        } $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
