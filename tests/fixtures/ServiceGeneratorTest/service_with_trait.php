<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use RonasIT\Support\Traits\EntityControlTrait;

/**
 * @property Post $model
 */
class PostService
{
    use EntityControlTrait;

    public function __construct()
    {
        $this->setModel(Post::class);
    }

    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this
            ->searchQuery($filters)
            ->filterBy('media_id')
            ->filterByQuery(['title', 'body'])
            ->getSearchResults();
    }
}
