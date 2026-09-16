<?php

namespace App\Http\Controllers;

use App\Actions\CreateTask;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\AttachmentService;
use App\Services\TaskPositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * "Tasks" page — personal workspace tasks only (unchanged behaviour).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'in:all,todo,in_progress,done'], 'priority' => ['nullable', 'in:all,low,medium,high'], 'deadline' => ['nullable', 'in:all,today,week,month,overdue']]);
        $tasks = Task::forUser($request->user())->applyFilters($filters)->with(['project:id,name', 'attachments'])->orderBy('deadline')->paginate(20)->withQueryString();

        return Inertia::render('Tasks/Index', ['tasks' => $tasks, 'filters' => $filters]);
    }

    /**
     * "My Tasks" page — cross-room: tasks assigned to user + personal tasks.
     */
    public function myTasks(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,todo,in_progress,done'],
            'priority' => ['nullable', 'in:all,low,medium,high'],
            'deadline' => ['nullable', 'in:all,today,week,month,overdue'],
            'room' => ['nullable'],
        ]);

        $tasks = Task::myTasks($user)
            ->applyFilters($filters)
            ->with(['project:id,name,room_id', 'project.room:id,name', 'attachments', 'assignees:id,name', 'creator:id,name'])
            ->orderBy('deadline')
            ->paginate(20)
            ->withQueryString();

        // Rooms the user belongs to, for the filter dropdown
        $rooms = $user->rooms()->select('rooms.id', 'rooms.name')->get();

        return Inertia::render('Tasks/MyTasks', [
            'tasks' => $tasks,
            'filters' => $filters,
            'rooms' => $rooms,
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project, CreateTask $action): RedirectResponse
    {
        $this->authorize('create', [Task::class, $project]);
        $action->execute($project, $request->validated(), $request->file('attachments', []));

        return back()->with('success', 'Task created successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task, AttachmentService $attachments, TaskPositionService $positions): RedirectResponse
    {
        $this->authorize('update', $task);
        $data = Arr::except($request->validated(), ['attachments', 'assignees']);
        if ($data['status'] !== $task->status) {
            $targetPosition = $task->project->tasks()->where('status', $data['status'])->count();
            $positions->move($task, $data['status'], $targetPosition);
        } $task->update(Arr::except($data, 'status'));

        // Sync assignees (only for room projects)
        $assigneeIds = $request->input('assignees', []);
        if ($task->project->room_id) {
            $task->assignees()->sync($assigneeIds ?: []);
        }

        foreach ($request->file('attachments', []) as $file) {
            $attachments->store($task, $file);
        }

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        Storage::disk('local')->deleteDirectory('attachments/'.$task->project->user_id.'/'.$task->id);
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
