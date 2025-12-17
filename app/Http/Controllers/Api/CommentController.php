<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Comment::class, 'comment');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $cacheKey = 'comments_index_' . md5(json_encode($request->all()));

        return CommentResource::collection(Cache::tags(['comments'])->remember($cacheKey, 600, function () use ($request) {
            return Comment::with(['author', 'post'])
                ->when($request->post_id, fn($q) => $q->where('post_id', $request->post_id))
                ->latest()
                ->paginate(10);
        }));
    }

    public function store(StoreCommentRequest $request): CommentResource
    {
        $comment = $request->user()->comments()->create($request->validated());

        Cache::tags(['comments', 'posts'])->flush();

        return new CommentResource($comment->load('author'));
    }

    public function show(Comment $comment): CommentResource
    {
        return new CommentResource($comment->load(['author']));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource
    {
        $comment->update($request->validated());

        Cache::tags(['comments'])->flush();

        return new CommentResource($comment->load('author'));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        Cache::tags(['comments', 'posts'])->flush();

        return response()->json(['message' => 'Comment deleted']);
    }
}
