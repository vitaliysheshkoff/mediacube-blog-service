<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory(10)->create();
        }

        $userIds = User::pluck('id');

        $posts = [];
        $now = now();

        for ($i = 0; $i < 50; $i++) {
            $posts[] = [
                'author_id' => $userIds->random(),
                'title' => fake()->sentence(),
                'body' => fake()->paragraphs(3, true),
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Post::insert($posts);
    }
}
