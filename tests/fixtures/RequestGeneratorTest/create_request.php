<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'is_draft' => 'boolean',
            'is_published' => 'boolean|present',
        ];
    }
}