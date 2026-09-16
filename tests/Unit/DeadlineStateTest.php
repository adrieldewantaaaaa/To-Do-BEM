<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_deadline_states_are_calculated_without_color_only_semantics(): void
    {
        $project = Project::factory()->for(User::factory())->create();
        $overdue = Task::factory()->for($project)->create(['deadline' => today()->subDay(), 'status' => 'todo']);
        $today = Task::factory()->for($project)->create(['deadline' => today(), 'status' => 'todo']);
        $upcoming = Task::factory()->for($project)->create(['deadline' => today()->addDays(3), 'status' => 'todo']);
        $done = Task::factory()->for($project)->create(['deadline' => today()->subDay(), 'status' => 'done']);
        $this->assertSame('overdue', $overdue->deadline_state);
        $this->assertSame('today', $today->deadline_state);
        $this->assertSame('upcoming', $upcoming->deadline_state);
        $this->assertSame('normal', $done->deadline_state);
    }
}
