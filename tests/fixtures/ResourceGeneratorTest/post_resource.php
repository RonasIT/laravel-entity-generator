<?php

namespace App\Http\Resources\Post;

use RonasIT\Support\Http\BaseResource;
use App\Models\Post;

/**
 * @property Post $resource
 */
class PostResource extends BaseResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
