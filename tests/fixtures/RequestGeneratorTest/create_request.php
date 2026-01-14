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
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}