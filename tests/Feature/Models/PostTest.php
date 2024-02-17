<?php

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;

it('uses title case for titles', function () {
    $post = Post::factory()->create(['title' => 'Hello, how is it going?']);

    expect($post->title)->toBe('Hello, How Is It Going?');
});

it('belongs to a user', function () {
    $post = Post::factory()
        ->forUser()
        ->create();

    expect($post->user)
        ->toBeInstanceOf(User::class);
});

it('has comments', function () {
    $post = Post::factory()
        ->hasComments(5)
        ->create();

    expect($post->comments)
        ->toHaveCount(5)
        ->each->toBeInstanceOf(Comment::class);
});

it('has images', function () {
    $post = Post::factory()
        ->hasImages(3)
        ->create();

    expect($post->images)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Image::class);
});
