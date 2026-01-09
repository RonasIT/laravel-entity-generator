<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use RonasIT\Support\Tests\Support\Command\Models\Forum\Post;

class SearchPostsRequest extends Request
{
    public function rules(): array
    {
        $availableRelations = implode(',', $this->getAvailableRelations());

        return [
            'desc' => 'boolean',
            'all' => 'boolean',
            'page' => 'integer',
            'per_page' => 'integer',
            'order_by' => 'string|in:' . $this->getOrderableFields(Post::class),
            'query' => 'string|nullable',
            'with.*' => 'required|string|in:' . $availableRelations,
            'with' => 'array',
        ];
    }

    //TODO: don't forget to review relations list
    protected function getAvailableRelations(): array
    {
        return [];
    }
}