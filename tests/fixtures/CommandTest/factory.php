<?php

namespace RonasIT\Support\Tests\Support\Command\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use RonasIT\Support\Tests\Support\Command\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
        ];
    }
}
