<?php

namespace RonasIT\Support\Tests\Support\Factory;

class ModelWithRelations
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
