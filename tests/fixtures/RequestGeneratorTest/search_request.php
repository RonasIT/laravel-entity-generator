<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class SearchPostsRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'integer|exists:users,id|required',
            'page' => 'integer',
            'per_page' => 'integer',
            'is_published' => 'boolean',
            'desc' => 'boolean',
            'all' => 'boolean',
            'with' => 'array',
            'order_by' => 'string',
            'query' => 'string|nullable',
            'with.*' => 'string',
        ];
    }
}