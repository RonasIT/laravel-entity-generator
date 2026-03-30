<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RonasIT\EntityGenerator\Tests\Support\Command\Factories\PostFactory;

class PostSeeder extends Seeder
{
    public function run()
    {
        PostFactory::new()->create();

    }
}
