<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RateLimitConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_read_endpoints_are_rate_limited()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user)->getJson('/api/posts')->assertStatus(200);
        }

        $this->actingAs($user)->getJson('/api/posts')->assertStatus(429);
    }

    public function test_cannot_create_duplicate_title_post_concurrency_simulation()
    {
        $user = User::factory()->create(['role' => 'editor']);

        // Create initial post to cause conflict
        Post::factory()->create(['title' => 'Unique Title', 'author_id' => $user->id]);

        $this->actingAs($user)
            ->postJson('/api/posts', [
                'title' => 'Unique Title',
                'body' => 'Another body',
                'status' => 'published',
            ])
            ->assertStatus(422)
            ->assertJson([
                'messages' => [
                    ['title' => ['The title has already been taken.']]
                ]
            ]);
    }

    public function test_cache_is_working_for_posts_index()
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'Post A', 'author_id' => $user->id, 'status' => 'published']);

        $this->actingAs($user)->getJson('/api/posts')
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'Post A']);

        Post::insert([
            'title' => 'Post B',
            'author_id' => $user->id,
            'body' => 'Body',
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->getJson('/api/posts')
            ->assertJsonFragment(['title' => 'Post A'])
            ->assertJsonMissing(['title' => 'Post B']);

        Cache::flush();

        $this->actingAs($user)->getJson('/api/posts')
            ->assertJsonFragment(['title' => 'Post B']);
    }
}
