<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RonasIT\Support\Tests\Support\Command\Factories\PostFactory;

class PostSeeder extends Seeder
{
    public function run()
    {
        PostFactory::new()->create();

    }
}
