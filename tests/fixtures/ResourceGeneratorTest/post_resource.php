<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use App\Models\Post;

/**
 * @property Post $resource
 */
final class PostResource extends BaseResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
