<?php

namespace RonasIT\Support\Tests\Support\Factory;

class ModelWithRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}