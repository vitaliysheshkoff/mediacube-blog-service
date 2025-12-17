<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts_with_structure()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'author_id' => $user->id,
            'title' => 'Test Post',
            'created_at' => now()->subDay(),
            'published_at' => now()->subDay(),
        ]);

        Comment::factory()->create([
            'post_id' => $post->id,
            'body' => 'Last Comment',
            'created_at' => now(),
        ]);

        $this->actingAs($user);
        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'body',
                        'published_at',
                        'status',
                        'author' => ['id', 'name', 'email'],
                        'comments_count',
                        'last_comment' => ['body', 'author' => ['id', 'name', 'email']],
                    ]
                ],
                'links',
                'meta',
            ]);

        $response->assertJsonFragment(['title' => 'Test Post']);
        $response->assertJsonFragment(['body' => 'Last Comment']);
    }

    public function test_filtering_and_sorting()
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'Alpha', 'published_at' => now()->subDays(2)]);
        Post::factory()->create(['title' => 'Beta', 'published_at' => now()->subDays(1)]);

        $this->actingAs($user);

        $response = $this->getJson('/api/posts?sort=title');
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertEquals('Alpha', $data[0]['title']);

        $response = $this->getJson('/api/posts?q=Alpha');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Alpha']);
    }
}
