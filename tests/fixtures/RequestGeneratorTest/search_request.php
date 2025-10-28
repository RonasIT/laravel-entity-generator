<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use App\Models\Post;

class SearchPostsRequest extends Request
{
    public function rules(): array
    {
        $availableRelations = implode(',', $this->getAvailableRelations());

        return [
            'user_id' => 'integer|exists:users,id|required',
            'page' => 'integer',
            'per_page' => 'integer',
            'is_published' => 'boolean',
            'desc' => 'boolean',
            'all' => 'boolean',
            'with' => 'array',
            'order_by' => 'string|in:' . $this->getOrderableFields(Post::class),
            'query' => 'string|nullable',
            'with.*' => 'string|in:' . $availableRelations,
        ];
    }

    protected function getAvailableRelations(): array
    {
        return [
            'comments',
            'user',
        ];
    }
}