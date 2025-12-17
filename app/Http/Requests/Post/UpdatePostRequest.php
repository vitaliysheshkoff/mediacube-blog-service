<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function rules(): array
    {
        $post = $this->route('post');
        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($post->id),
            ],
            'body' => 'sometimes|string',
            'published_at' => 'nullable|date',
            'status' => 'sometimes|in:draft,published',
        ];
    }
}
