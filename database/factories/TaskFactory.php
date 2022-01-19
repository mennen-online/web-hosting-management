<?php

namespace Database\Factories;

use App\Models\TaskTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task_topic_id' => TaskTopic::factory()->create(),
            'title' => $this->faker->words(asText: true),
            'content' => $this->faker->words(asText: true)
        ];
    }

    public function withToDoBy() {
        return $this->state(function (array $attributes) {
            return [
                'to_do_by' => $this->faker->dateTimeBetween('now', '+1 years')
            ];
        });
    }
}
