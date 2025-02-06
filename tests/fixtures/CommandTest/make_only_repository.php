<?php

namespace App\Repositories;

use App\Models\Post;
use RonasIT\Support\Repositories\BaseRepository;

/**
 * @property Post $model
 */
class PostRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(Post::class);
    }
}
