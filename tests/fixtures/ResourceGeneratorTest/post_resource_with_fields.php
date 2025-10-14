<?php

namespace App\Http\Resources\Post;

use RonasIT\Support\Http\BaseResource;
use App\Models\Post;

/**
 * @property Post $resource
 */
class PostResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'owner_id' => $this->resource->owner_id,
        ];
    }
}
