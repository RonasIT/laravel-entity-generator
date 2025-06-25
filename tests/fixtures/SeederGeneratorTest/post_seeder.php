<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\PostFactory;

class PostSeeder extends Seeder
{
    public function run()
    {
        PostFactory::new()->make([
            'user_id' => \Database\Factories\UserFactory::new()->create()->id,
        ]);

        \Database\Factories\CommentFactory::new()->count(10)->make([
            'post_id' => $post->id,
        ]);

    }
}
