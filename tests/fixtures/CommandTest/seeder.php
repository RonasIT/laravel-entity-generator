<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RonasIT\Support\Tests\Support\Command\Models\Post;

class PostSeeder extends Seeder
{
    public function run()
    {
        Post::factory()->create();

    }
}