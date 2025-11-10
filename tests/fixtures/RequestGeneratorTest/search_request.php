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
            'user_id' => 'required|integer|exists:users,id',
            'page' => 'integer',
            'per_page' => 'integer',
            'is_published' => 'boolean',
            'desc' => 'boolean',
            'all' => 'boolean',
            'order_by' => 'string|in:' . $this->getOrderableFields(Post::class),
            'query' => 'string|nullable',
            'with' => 'array',
            'with.*' => 'required|string|in:' . $availableRelations,
        ];
    }

    //TODO: don't forget to review relations list
    protected function getAvailableRelations(): array
    {
        return [
            'comments',
            'user',
        ];
    }
}