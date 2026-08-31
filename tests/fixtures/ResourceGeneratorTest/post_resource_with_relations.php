<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use App\Models\Post;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Role\RolesCollectionResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\Tag\TagsCollectionResource;

/**
 * @property Post $resource
 */
final class PostResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'comment' => CommentResource::make($this->whenLoaded('comment')),
            'roles' => RolesCollectionResource::make($this->whenLoaded('roles')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'tags' => TagsCollectionResource::make($this->whenLoaded('tags')),
        ];
    }
}
