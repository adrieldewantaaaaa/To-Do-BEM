<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Services\TaskPositionService;
use Illuminate\Http\RedirectResponse;

class TaskStatusController extends Controller
{
    public function __invoke(UpdateTaskStatusRequest $request, Task $task, TaskPositionService $positions): RedirectResponse
    {
        $this->authorize('update', $task);
        $positions->move($task, $request->string('status')->toString(), $request->integer('position'));

        return back()->with('success', 'Task moved successfully.');
    }
}
