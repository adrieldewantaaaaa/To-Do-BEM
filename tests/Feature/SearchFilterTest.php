<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_filters_can_be_combined(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        Task::factory()->for($project)->create(['title' => 'Launch landing page', 'status' => 'in_progress', 'priority' => 'high', 'deadline' => today()->addDays(2)]);
        Task::factory()->for($project)->create(['title' => 'Write notes', 'status' => 'todo', 'priority' => 'low', 'deadline' => today()->addMonth()]);
        $this->actingAs($user)->get(route('tasks.index', ['search' => 'Launch', 'status' => 'in_progress', 'priority' => 'high', 'deadline' => 'week']))->assertInertia(fn (Assert $page) => $page->component('Tasks/Index')->has('tasks.data', 1)->where('tasks.data.0.title', 'Launch landing page'));
    }

    public function test_global_search_is_scoped_to_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Project::factory()->for($user)->create(['name' => 'Private Alpha']);
        Project::factory()->for($other)->create(['name' => 'Private Alpha outsider']);
        $this->actingAs($user)->get(route('search', ['q' => 'Private Alpha']))->assertInertia(fn (Assert $page) => $page->component('Search/Index')->has('projects', 1));
    }
}
