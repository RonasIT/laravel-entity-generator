<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Post;

/**
 * @property Post $resource
 */
class PostResource extends JsonResource
{
    public static $wrap = null;

    //TODO implement custom serialization logic or remove method redefining
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
