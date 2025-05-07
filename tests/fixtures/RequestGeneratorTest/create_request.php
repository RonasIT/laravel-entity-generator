<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;

class CreatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'integer|exists:users,id|required',
            'is_draft' => 'boolean',
            'is_published' => 'boolean|present',
        ];
    }
}