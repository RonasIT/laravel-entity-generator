<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|db_type_range:integer|exists:users,id',
            'views_count' => 'required|integer|db_type_range:integer',
            'is_draft' => 'boolean',
            'phone' => 'string|db_type_range:varchar',
            'name' => 'required|string|db_type_range:varchar',
            'is_published' => 'boolean|present',
        ];
    }
}