<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_user_can_list_posts()
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['author_id' => $user->id, 'status' => 'published']);

        $response = $this->actingAs($user)->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'body', 'author', 'comments_count', 'last_comment']
                ]
            ]);
    }

    public function test_editor_can_create_post()
    {
        $editor = User::factory()->create(['role' => 'editor']);

        Sanctum::actingAs($editor, $editor->getAbilities());

        $response = $this->postJson('/api/posts', [
            'title' => 'New Post',
            'body' => 'Post Content',
            'status' => 'published',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Post']);
    }

    public function test_viewer_cannot_create_post()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        Sanctum::actingAs($viewer, $viewer->getAbilities());

        $response = $this->postJson('/api/posts', [
            'title' => 'New Post',
            'body' => 'Post Content',
        ]);

        $response->assertStatus(403);
    }

    public function test_editor_can_update_own_post()
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $post = Post::factory()->create(['author_id' => $editor->id]);

        Sanctum::actingAs($editor, $editor->getAbilities());

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_editor_cannot_update_others_post()
    {
        $editor1 = User::factory()->create(['role' => 'editor']);
        $editor2 = User::factory()->create(['role' => 'editor']);
        $post = Post::factory()->create(['author_id' => $editor2->id]);

        Sanctum::actingAs($editor1, $editor1->getAbilities());

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_post()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);

        Sanctum::actingAs($admin, $admin->getAbilities());

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Admin Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Admin Updated']);
    }
}
