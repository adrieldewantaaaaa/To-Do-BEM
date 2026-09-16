<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_is_zero_without_tasks(): void
    {
        $project = Project::factory()->for(User::factory())->create();
        $this->assertSame(0, app(ProjectProgressService::class)->calculate($project));
    }

    public function test_progress_is_percentage_of_done_tasks(): void
    {
        $project = Project::factory()->for(User::factory())->create();
        Task::factory()->count(3)->for($project)->create(['status' => 'done']);
        Task::factory()->for($project)->create(['status' => 'todo']);
        $this->assertSame(75, app(ProjectProgressService::class)->calculate($project));
    }
}
