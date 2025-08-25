<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class SearchPostsRequest extends Request
{
    public function rules(): array
    {
        return [
            'page' => 'integer',
            'per_page' => 'integer',
            'order_by' => 'string',
            'desc' => 'boolean',
            'all' => 'boolean',
            'with' => 'array',
            'query' => 'string|nullable',
            'with.*' => 'string',
        ];
    }
}