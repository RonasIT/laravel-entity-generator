<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use RonasIT\Support\Tests\Support\Command\Models\Post;

class SearchPostsRequest extends Request
{
    public function rules(): array
    {
        return [
            'page' => 'integer',
            'per_page' => 'integer',
            'order_by' => 'string|in:' . $this->getOrderableFields(Post::class),
            'desc' => 'boolean',
            'all' => 'boolean',
            'with' => 'array',
            'query' => 'string|nullable',
            'with.*' => 'string',
        ];
    }
}