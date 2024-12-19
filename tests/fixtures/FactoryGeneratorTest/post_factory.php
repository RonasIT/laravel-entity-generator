<?php

namespace Database\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $faker = app(Faker::class);

        return [
            'author_id' => 1,
            'user_id' => 1,
            'title' => $faker->title,
            'iban' => $faker->iban,
            'something' => $faker->word,
            'json_text' => [],
        ];
    }
}
