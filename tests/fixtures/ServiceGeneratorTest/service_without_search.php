<?php

namespace App\Services;

use App\Repositories\PostRepository;
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
}
