<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    public function index()
    {
        $posts = Post::with(['author'])
            ->withCount('comments')
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $post = new Post($validated);
        $post->author_id = $request->user()->id;
        $post->save();

        return new PostResource($post->load('author'));
    }

    public function show(Post $post)
    {
        return new PostResource($post->load(['author', 'comments' => fn($q) => $q->latest()]));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return new PostResource($post->load('author'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
