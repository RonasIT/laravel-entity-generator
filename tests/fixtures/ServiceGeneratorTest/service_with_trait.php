<?php

namespace App\Services;

use App\Models\Post;
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

    public function search($filters)
    {
        return $this->searchQuery($filters)
        ->filterBy('media_id')
        ->filterByQuery(['title', 'body'])
        ->getSearchResults();
    }
}
