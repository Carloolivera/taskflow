<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => 'urgent']);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => 'pending',
        ]);
    }
}
