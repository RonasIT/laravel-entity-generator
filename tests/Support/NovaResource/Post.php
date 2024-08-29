<?php

namespace RonasIT\Support\Tests\Support\NovaResource;

class Post
{
    public function getConnectionName(): string
    {
        return 'pgsql';
    }

    public function getTable(): string
    {
        return 'posts';
    }
}