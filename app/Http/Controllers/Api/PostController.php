<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\IndexPostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

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

        $cacheKey = 'posts_index_' . md5(json_encode($request->all()));

        $posts = Cache::tags(['posts'])->remember($cacheKey, 3600, function () use ($request, $field, $direction) {
            return Post::query()
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
        });

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): PostResource
    {
        $post = new Post($request->validated());
        $post->author_id = $request->user()->id;

        try {
            $post->save();
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') { // Postgres unique violation
                throw ValidationException::withMessages([
                    'title' => ['The title has already been taken.'],
                ]);
            }
        }

        Cache::tags(['posts'])->flush();

        return new PostResource($post->load('author'));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource(Cache::tags(['posts'])->remember('posts_' . $post->id, 3600, function () use ($post) {
            return $post->load(['author', 'lastComment.author']);
        }));
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        try {
            $post->update($request->validated());
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'title' => ['The title has already been taken.'],
                ]);
            }
        }

        Cache::tags(['posts'])->flush();

        return new PostResource($post->load('author'));
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        Cache::tags(['posts'])->flush();

        return response()->json(['message' => 'Post deleted']);
    }
}
