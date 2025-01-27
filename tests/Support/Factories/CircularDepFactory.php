<?php

namespace RonasIT\Support\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use RonasIT\Support\Tests\Support\Test\CircularDep;

class CircularDepFactory extends Factory
{
    protected $model = CircularDep::class;

    public function definition(): array
    {
        return [
            'title' => 'some title',
            'body' => 'some body',
            'posted_at' => Carbon::now(),
            'drafted' => false,
            'data' => [
                'title' => '1',
                'body' => '2',
            ],
        ];
    }
}
