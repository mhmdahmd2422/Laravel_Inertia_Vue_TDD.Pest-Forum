<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id' => $this->id,
          'user' => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
          'images' => $this->whenLoaded('images', fn() => ImageResource::collection($this->images)),
          'post' => $this->whenLoaded('post', fn() => PostResource::make($this->post)),
          'body' => $this->body,
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at,
          'can' => [
              'delete' => $request->user()?->can('delete', $this->resource),
              'update' => $request->user()?->can('update', $this->resource),
          ],
          'author' => auth()->check() ? $this->user_id === auth()->user()->id : null,
        ];
    }
}
