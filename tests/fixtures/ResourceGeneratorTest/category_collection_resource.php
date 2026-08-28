<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class CategoriesCollectionResource extends ResourceCollection
{
    public $collects = CategoryResource::class;
}
