<?php

use Illuminate\Database\Seeder;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Post::class, 10)->create()->each(function ($post) {
            $post->comments()->saveMany(factory(\App\Comment::class, 5)->make());
        });

        factory(\App\Video::class, 10)->create()->each(function ($post) {
            $post->comments()->saveMany(factory(\App\Comment::class, 5)->make());
        });
    }
}
