<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PostsCollectionResource extends ResourceCollection
{
    public $collects = PostResource::class;
}