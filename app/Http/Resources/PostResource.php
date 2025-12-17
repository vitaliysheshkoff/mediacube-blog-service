<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'published_at' => $this->published_at,
            'status' => $this->status,
            'author' => new UserResource($this->whenLoaded('author')),
            'comments_count' => $this->whenCounted('comments'),
            'last_comment' => $this->whenLoaded('lastComment', function () {
                return [
                    'body' => $this->lastComment->body,
                    'author' => new UserResource($this->lastComment->author),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
