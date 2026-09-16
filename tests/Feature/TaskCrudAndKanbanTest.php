<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudAndKanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_edit_move_and_delete_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $this->actingAs($user)->post(route('projects.tasks.store', $project), ['title' => 'Design wireframe', 'description' => 'Core screens', 'deadline' => '2026-09-20', 'status' => 'todo', 'priority' => 'high'])->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $this->actingAs($user)->put(route('tasks.update', $task), ['title' => 'Design final wireframe', 'description' => 'Core screens', 'deadline' => '2026-09-21', 'status' => 'todo', 'priority' => 'high'])->assertSessionHasNoErrors();
        $this->actingAs($user)->patch(route('tasks.status', $task), ['status' => 'in_progress', 'position' => 0])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress', 'position' => 0]);
        $this->actingAs($user)->delete(route('tasks.destroy', $task))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
