<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use App\Services\PostService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdatePostRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_published' => 'boolean',
            'is_draft' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'media_id' => 'filled|integer|exists:media,id',
            'seo_score' => 'nullable|numeric',
            'rating' => 'filled|numeric',
            'description' => 'nullable|string',
            'title' => 'filled|string|unique:posts,title,' . $this->route('id'),
            'reviewed_at' => 'nullable|date',
            'published_at' => 'filled|date',
            'meta' => 'nullable|array',
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