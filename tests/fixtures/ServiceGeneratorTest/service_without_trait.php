<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Arr;
use RonasIT\Support\Services\EntityService;

/**
 * @mixin PostRepository
 * @property PostRepository $repository
 */
class PostService extends EntityService
{
    public function __construct()
    {
        $this->setRepository(PostRepository::class);
    }

    public function search($filters)
    {
        return $this
            ->with(Arr::get($filters, 'with', []))
            ->withCount(Arr::get($filters, 'with_count', []))
            ->searchQuery($filters)
            ->filterBy('media_id')
            ->filterByQuery(['title', 'body'])
            ->getSearchResults();
    }
}
