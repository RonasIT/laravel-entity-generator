<?php

namespace RonasIT\Support\Tests\Support\Factory;

class ModelWithRelations
{
    public function Post()
    {
        return $this->belongsTo(Post::class);
    }
}
