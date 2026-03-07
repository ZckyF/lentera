<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => ChatSession::factory(),
            'sender' => fake()->randomElement(['user', 'ai']),
            'message' => fake()->sentence(),
            'doc_reference' => fake()->optional(0.3)->passthrough([
                'document_id' => fake()->numberBetween(1, 100),
                'page' => fake()->numberBetween(1, 50),
            ]),
        ];
    }

    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => ['sender' => 'user']);
    }

    public function fromAi(): static
    {
        return $this->state(fn (array $attributes) => ['sender' => 'ai']);
    }
}
