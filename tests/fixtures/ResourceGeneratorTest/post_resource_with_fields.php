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
            'priority' => $this->resource->priority,
            'media_id' => $this->resource->media_id,
            'seo_score' => $this->resource->seo_score,
            'rating' => $this->resource->rating,
            'description' => $this->resource->description,
            'title' => $this->resource->title,
            'is_reviewed' => $this->resource->is_reviewed,
            'is_published' => $this->resource->is_published,
            'meta' => $this->resource->meta,
            'reviewed_at' => $this->resource->reviewed_at,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'published_at' => $this->resource->published_at,
        ];
    }
}
