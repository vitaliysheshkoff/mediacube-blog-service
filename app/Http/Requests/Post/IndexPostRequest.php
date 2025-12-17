<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class IndexPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sort' => 'nullable|in:published_at,title,created_at',
            'q' => 'nullable|string|min:1',
            'status' => 'nullable|in:published,draft,archived',
            'published_at.from' => 'nullable|date',
            'published_at.to' => 'nullable|date|after_or_equal:published_at.from',
        ];
    }
}
