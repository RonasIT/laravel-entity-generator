<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\Request;
use App\Services\PostService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use RonasIT\Support\Http\BaseRequest;

class GetPostRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'with' => 'array',
            'with.*' => 'string|required',
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