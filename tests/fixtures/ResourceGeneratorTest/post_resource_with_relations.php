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
            'comment' => CommentResource::make($this->resource->comment),
            'roles' => RolesCollectionResource::make($this->resource->roles),
            'user' => UserResource::make($this->resource->user),
            'tags' => TagsCollectionResource::make($this->resource->tags),
        ];
    }
}
