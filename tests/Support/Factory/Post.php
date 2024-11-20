<?php

namespace RonasIT\Support\Tests\Support\Factory;

class Post
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
