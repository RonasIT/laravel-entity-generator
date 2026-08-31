<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use App\Models\Category;

/**
 * @property Category $resource
 */
final class CategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'categories' => CategoriesCollectionResource::make($this->whenLoaded('categories')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
