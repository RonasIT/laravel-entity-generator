<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_published' => 'boolean|present',
            'is_draft' => 'boolean',
            'priority' => 'integer',
            'media_id' => 'required|integer|exists:media,id',
            'seo_score' => 'numeric',
            'rating' => 'required|numeric',
            'description' => 'string',
            'title' => 'required|string',
            'reviewed_at' => 'date',
            'published_at' => 'required|date',
            'meta' => 'array',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}