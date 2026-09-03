<?php

namespace Database\Factories;

use App\Models\StaffTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffTask>
 */
class StaffTaskFactory extends Factory
{
    protected $model = StaffTask::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'category' => 'management',
            'priority' => 'normal',
            'status' => 'open',
            'assigned_to' => User::factory(),
            'assigned_by' => User::factory(),
        ];
    }
}
