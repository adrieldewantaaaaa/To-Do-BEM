<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_project_or_task(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->for($owner)->create();
        $task = Task::factory()->for($project)->create();
        $this->actingAs($stranger)->get(route('projects.show', $project))->assertForbidden();
        $this->actingAs($stranger)->put(route('tasks.update', $task), ['title' => 'Hijacked', 'description' => null, 'deadline' => '2026-09-21', 'status' => 'done', 'priority' => 'low'])->assertForbidden();
        $this->actingAs($stranger)->delete(route('projects.destroy', $project))->assertForbidden();
    }
}
