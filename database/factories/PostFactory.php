<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => $this->faker->unique()->sentence,
            'body' => $this->faker->paragraph,
            'published_at' => now(),
            'status' => 'published',
        ];
    }
}
