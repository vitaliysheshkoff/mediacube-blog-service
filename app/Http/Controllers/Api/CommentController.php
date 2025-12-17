<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Comment::class, 'comment');
    }

    public function index(Request $request)
    {
        $query = Comment::with(['author', 'post']);

        if ($request->has('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        $comments = $query->latest()->paginate(10);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request)
    {
        $validated = $request->validated();

        $comment = new Comment($validated);
        $comment->author_id = $request->user()->id;
        $comment->save();

        return new CommentResource($comment->load('author'));
    }

    public function show(Comment $comment)
    {
        return new CommentResource($comment->load(['author']));
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return new CommentResource($comment->load('author'));
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
