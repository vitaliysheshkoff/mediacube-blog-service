<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\IndexPostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    public function index(IndexPostRequest $request): AnonymousResourceCollection
    {
        $sortMap = [
            'title' => ['title', 'asc'],
            'created_at' => ['created_at', 'desc'],
            'published_at' => ['published_at', 'desc'],
        ];

        [$field, $direction] = $sortMap[$request->input('sort')] ?? $sortMap['published_at'];

        $posts = Post::query()
            ->with(['author', 'lastComment.author'])
            ->withCount('comments')
            ->search($request->input('q'))
            ->when($request->input('status'), fn($q, $status) => $q->where('status', $status))
            ->publishedBetween(
                $request->input('published_at.from'),
                $request->input('published_at.to')
            )
            ->orderBy($field, $direction)
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): PostResource
    {
        $post = new Post($request->validated());
        $post->author_id = $request->user()->id;
        $post->save();

        return new PostResource($post->load('author'));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load(['author', 'lastComment.author']));
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $post->update($request->validated());

        return new PostResource($post->load('author'));
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
