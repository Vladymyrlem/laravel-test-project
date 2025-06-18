<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_name' => Str::studly($this->faker->name), // JeniferWindler
            'email' => $this->faker->unique()->safeEmail, // обов’язкове
            'homepage' => $this->faker->boolean(50) ? $this->faker->url : null, // іноді є, іноді ні
            'text' => $this->withRandomTags($this->faker->paragraph),
            'created_at' => now()->subMinutes(rand(1, 4320)),
        ];
    }

    // Функція для додавання випадкових HTML-тегів
    private function withRandomTags(string $text): string
    {
        $tags = ['<strong>%s</strong>', '<em>%s</em>', '<code>%s</code>', '<i>%s</i>', '<a href="https://example.com" title="Example">%s</a>'];

        $words = explode(' ', $text);
        for ($i = 0; $i < count($words); $i++) {
            if (rand(0, 10) > 8) { // приблизно 20% шанс вставити тег
                $template = $this->faker->randomElement($tags);
                $words[$i] = sprintf($template, $words[$i]);
            }
        }

        return implode(' ', $words);
    }
}
