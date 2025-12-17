<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'post_id' => 'required|exists:posts,id',
            'body' => 'required|string',
        ];
    }
}
