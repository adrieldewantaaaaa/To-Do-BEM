<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return ['project_id' => Project::factory(), 'title' => fake()->sentence(4), 'description' => fake()->paragraph(), 'deadline' => fake()->dateTimeBetween('now', '+1 month'), 'status' => fake()->randomElement(['todo', 'in_progress', 'done']), 'priority' => fake()->randomElement(['low', 'medium', 'high']), 'position' => fake()->numberBetween(0, 10)];
    }
}
