<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_delete_project(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/projects', ['name' => 'Website Redesign', 'description' => 'Refresh the public site', 'deadline' => '2026-09-30', 'status' => 'active']);
        $project = Project::firstOrFail();
        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['user_id' => $user->id, 'name' => 'Website Redesign']);
        $this->actingAs($user)->put(route('projects.update', $project), ['name' => 'Website Refresh', 'description' => 'Updated', 'deadline' => '2026-10-10', 'status' => 'completed'])->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'completed']);
        $this->actingAs($user)->delete(route('projects.destroy', $project))->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
