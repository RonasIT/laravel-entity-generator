<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

final class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_published' => 'present|boolean',
            'is_draft' => 'boolean',
            'priority' => 'integer|db_type_range:integer',
            'media_id' => 'required|integer|db_type_range:integer|exists:media,id',
            'seo_score' => 'numeric|db_type_range:double',
            'rating' => 'required|numeric|db_type_range:double',
            'description' => 'string|db_type_range:varchar',
            'title' => 'required|string|db_type_range:varchar|unique:posts,title',
            'reviewed_at' => 'date',
            'published_at' => 'required|date',
            'meta' => 'array',
            'user_id' => 'required|integer|db_type_range:integer|exists:users,id',
        ];
    }
}