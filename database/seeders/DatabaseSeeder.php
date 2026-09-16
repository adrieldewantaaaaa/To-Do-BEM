<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create(['name' => 'Dina Putri', 'email' => 'demo@tdb.test']);
        Project::factory()->count(4)->for($user)->create()->each(fn (Project $project) => Task::factory()->count(6)->for($project)->create());
    }
}
