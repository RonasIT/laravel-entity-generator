<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'title' => 'some title',
            'body' => 'some body',
            'posted_at' => Carbon::now(),
            'drafted' => false,
            'data' => ['title' => '1', 'body' => '2']
        ];
    }
}
