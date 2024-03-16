<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
          'name' => $this->name,
          'email' => $this->when($this->id === $request->user()?->id, $this->email),
          'created_at' => $this->when($this->id === $request->user()?->id, $this->created_at),
          'updated_at' => $this->when($this->id === $request->user()?->id, $this->updated_at),
          'profile_photo_url' => $this->profile_photo_url,
        ];
    }
}
