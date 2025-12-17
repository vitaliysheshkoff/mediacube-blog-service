<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::all();
        $users = User::pluck('id');

        if ($posts->isEmpty() || $users->isEmpty()) {
            return;
        }

        $allComments = [];

        foreach ($posts as $post) {
            $numComments = random_int(1, 10);

            for ($i = 0; $i < $numComments; $i++) {
                $allComments[] = [
                    'post_id' => $post->id,
                    'author_id' => $users->random(),
                    'body' => fake()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Comment::insert($allComments);
    }
}
