<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    public function run()
    {
        Post::factory()->make([
            'user_id' => \App\Models\User::factory()->create()->id,
        ]);

        \App\Models\Comment::factory()->count(10)->make([
            'post_id' => $post->id,
        ]);

    }
}