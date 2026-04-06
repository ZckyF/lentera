<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ext = fake()->randomElement(['pdf', 'docx']);
        $mime = 'application/pdf';

        return [
            'title' => fake()->sentence(4),
            'year' => fake()->numberBetween(2020, 2026),
            'file_path' => 'documents/' . fake()->uuid() . '.' . $ext,
            'mime_type' => $mime,
            'page_count' => fake()->numberBetween(1, 100),
            'file_size' => fake()->numberBetween(50_000, 5_000_000),
            'content_raw' => fake()->optional(0.7)->paragraphs(3, true),
            'status' => fake()->randomElement(['active', 'inactive']),
            'uploaded_by' => User::factory(),
        ];
    }
}
