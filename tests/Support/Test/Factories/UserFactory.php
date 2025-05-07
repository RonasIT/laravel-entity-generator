<?php

namespace RonasIT\Support\Tests\Support\Test\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RonasIT\Support\Tests\Support\Test\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => 'some name',
            'email' => 'some email',
        ];
    }
}
