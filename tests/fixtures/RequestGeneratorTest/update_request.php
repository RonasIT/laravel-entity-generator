<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use App\Services\PostService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|db_type_range:integer|exists:users,id',
            'views_count' => 'integer|db_type_range:integer',
            'is_draft' => 'boolean',
            'is_published' => 'boolean',
            'phone' => 'string|db_type_range:varchar',
            'name' => 'string|db_type_range:varchar',
        ];
    }

    public function validateResolved(): void
    {
        parent::validateResolved();

        $service = app(PostService::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'Post']));
        }
    }
}