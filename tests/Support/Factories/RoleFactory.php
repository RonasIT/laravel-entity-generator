<?php

namespace RonasIT\Support\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RonasIT\Support\Tests\Support\Test\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => 'some name',
        ];
    }
}
