<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'user_name' => $this->faker->userName,
            'email' => $this->faker->safeEmail,
            'homepage' => $this->faker->url,
            'text' => '<p>' . $this->faker->paragraph . '</p>',
            'parent_id' => null,
        ];
    }
}
