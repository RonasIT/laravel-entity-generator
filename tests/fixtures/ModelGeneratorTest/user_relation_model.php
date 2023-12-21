<?php

namespace RonasIT\Support\Tests\Support\Model;

class RelationModelMock
{
    public function some_relation()
    {

    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
