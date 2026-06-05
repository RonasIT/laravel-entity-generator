<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class PostsCollectionResource extends ResourceCollection
{
    public $collects = PostResource::class;
}
