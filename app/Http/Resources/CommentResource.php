<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => $this->created_at,
        ];
    }
}
