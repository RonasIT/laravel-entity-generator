<?php

namespace RonasIT\EntityGenerator\Tests\Support\Test\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RonasIT\EntityGenerator\Tests\Support\Test\Models\Role;

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
