<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Home/Feed (redirect authenticated users to feed)
Route::get('/home', [App\Http\Controllers\PostController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

// Dashboard for analytics
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Keep posts route for backward compatibility, but redirect to home for authenticated users
Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('posts.show');
    Route::post('/posts/{post}/vote', [App\Http\Controllers\VoteController::class, 'votePost'])->name('votes.post');
    Route::post('/posts/{post}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/best-answer', [App\Http\Controllers\CommentController::class, 'markBestAnswer'])->name('comments.best-answer');
    Route::post('/comments/{comment}/vote', [App\Http\Controllers\VoteController::class, 'voteComment'])->name('votes.comment');
    Route::post('/posts/{post}/validate', [App\Http\Controllers\IdeaValidationController::class, 'store'])->name('idea-validations.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
});

require __DIR__.'/auth.php';
