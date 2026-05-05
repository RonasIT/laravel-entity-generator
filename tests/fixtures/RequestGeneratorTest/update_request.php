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
            'is_published' => 'boolean',
            'is_draft' => 'boolean',
            'priority' => 'integer',
            'media_id' => 'filled|integer|exists:media,id',
            'seo_score' => 'numeric',
            'rating' => 'filled|numeric',
            'description' => 'string',
            'title' => 'filled|string|unique:posts,title,' . $this->route('id'),
            'reviewed_at' => 'date',
            'published_at' => 'filled|date',
            'meta' => 'array',
            'user_id' => 'filled|integer|exists:users,id',
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