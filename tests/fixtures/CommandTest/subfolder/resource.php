<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use RonasIT\EntityGenerator\Tests\Support\Command\Models\Forum\Post;

/**
 * @property Post $resource
 */
class PostResource extends BaseResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
