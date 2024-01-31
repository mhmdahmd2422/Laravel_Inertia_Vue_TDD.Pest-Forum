<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentImageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TemporaryImageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::post('/upload', [TemporaryImageController::class, 'store']);
    Route::delete('/revert/{fileName}', [TemporaryImageController::class, 'destroy']);
    Route::delete('/image/{commentImage}', CommentImageController::class)
        ->name('image.destroy');
    Route::resource('posts.comments', CommentController::class)
        ->shallow()->only(['store', 'update', 'destroy']);
});

Route::get('posts', [PostController::class, 'index'])
    ->name('posts.index');

Route::get('posts/{post}', [PostController::class, 'show'])
    ->name('posts.show');
