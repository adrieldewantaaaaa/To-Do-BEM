<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'name' => fake()->words(3, true), 'description' => fake()->sentence(), 'deadline' => fake()->dateTimeBetween('+1 week', '+3 months'), 'status' => 'active'];
    }
}
