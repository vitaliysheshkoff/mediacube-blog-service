<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_posts()
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'First Post', 'author_id' => $user->id, 'status' => 'published']);
        Post::factory()->create(['title' => 'Second Post', 'author_id' => $user->id, 'status' => 'published']);

        Sanctum::actingAs($user, $user->getAbilities());

        $response = $this->getJson('/api/posts?q=First');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'First Post']);
    }

    public function test_can_filter_posts_by_status()
    {
        $user = User::factory()->create();
        Post::factory()->create(['status' => 'published', 'author_id' => $user->id]);
        Post::factory()->create(['status' => 'draft', 'author_id' => $user->id]);

        Sanctum::actingAs($user, $user->getAbilities());

        $response = $this->getJson('/api/posts?status=published');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_unique_title_validation()
    {
        $user = User::factory()->create(['role' => 'editor']);
        $abilities = $user->getAbilities();

        Post::factory()->create(['title' => 'Duplicate Title', 'author_id' => $user->id]);

        Sanctum::actingAs($user, $abilities);

        $response = $this->postJson('/api/posts', [
            'title' => 'Duplicate Title',
            'body' => 'Content',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'messages' => [
                    [
                        'title' => ['The title has already been taken.']
                    ]
                ]
            ]);
    }

    public function test_meta_roles_endpoint()
    {
        $response = $this->getJson('/api/meta/roles');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
