<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use App\Models\Post;
use App\Http\Resources\User\UserResource;

/**
 * @property Post $resource
 */
final class PostResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'user' => UserResource::make($this->resource->user),
        ];
    }
}
