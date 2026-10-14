<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

final class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_published' => 'present|boolean',
            'is_draft' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'media_id' => 'required|integer|exists:media,id',
            'seo_score' => 'nullable|numeric',
            'rating' => 'required|numeric',
            'description' => 'nullable|string',
            'title' => 'required|string|unique:posts,title',
            'reviewed_at' => 'nullable|date',
            'published_at' => 'required|date',
            'meta' => 'nullable|array',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}