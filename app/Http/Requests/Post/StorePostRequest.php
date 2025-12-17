<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:posts',
            'body' => 'required|string',
            'published_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ];
    }

    public function prepareForValidation(): void
    {
        if (!$this->has('status')) {
            $this->merge(['status' => 'draft']);
        }
    }
}
