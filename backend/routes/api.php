<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Modules\Forum\Http\Controllers\AdminDashboardController;
use App\Modules\Forum\Http\Controllers\AdminPostController;
use App\Modules\Forum\Http\Controllers\AdminTagController;
use App\Modules\Forum\Http\Controllers\AdminThreadController;
use App\Modules\Forum\Http\Controllers\BookmarkController;
use App\Modules\Forum\Http\Controllers\CategoryAdminController;
use App\Modules\Forum\Http\Controllers\CategoryController;
use App\Modules\Forum\Http\Controllers\FollowController;
use App\Modules\Forum\Http\Controllers\ModerationController;
use App\Modules\Forum\Http\Controllers\PostController;
use App\Modules\Forum\Http\Controllers\ProfileController;
use App\Modules\Forum\Http\Controllers\ReportController;
use App\Modules\Forum\Http\Controllers\StatsController;
use App\Modules\Forum\Http\Controllers\TagController;
use App\Modules\Forum\Http\Controllers\ThreadController;
use App\Modules\Forum\Http\Controllers\UserAdminController;
use App\Modules\Forum\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)->middleware('throttle:10,1')->name('register');

Route::get('/user', function () {
    return request()->user();
})->middleware('auth:api');

Route::middleware(['auth:api', 'not_banned'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('read-all', [NotificationController::class, 'readAll'])->name('read-all');
    Route::post('{id}/read', [NotificationController::class, 'read'])->name('read');
});

Route::prefix('forum')->name('forum.')->group(function () {
    // public reads
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    Route::get('tags', [TagController::class, 'index'])->name('tags.index');

    Route::get('threads', [ThreadController::class, 'index'])->name('threads.index');
    Route::get('threads/{thread}', [ThreadController::class, 'show'])->name('threads.show');

    Route::get('threads/{thread}/posts', [PostController::class, 'index'])->name('threads.posts.index');

    // anything that writes needs a token and a non-banned account
    Route::middleware(['auth:api', 'not_banned'])->group(function () {
        Route::post('threads', [ThreadController::class, 'store'])->middleware('throttle:6,1')->name('threads.store');
        Route::put('threads/{thread}', [ThreadController::class, 'update'])->name('threads.update');
        Route::delete('threads/{thread}', [ThreadController::class, 'destroy'])->name('threads.destroy');

        Route::post('threads/{thread}/posts', [PostController::class, 'store'])->middleware('throttle:12,1')->name('threads.posts.store');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        Route::post('threads/{thread}/vote', [VoteController::class, 'thread'])->middleware('throttle:30,1')->name('threads.vote');
        Route::post('posts/{post}/vote', [VoteController::class, 'post'])->middleware('throttle:30,1')->name('posts.vote');

        Route::post('threads/{thread}/bookmark', [BookmarkController::class, 'toggle'])->middleware('throttle:30,1')->name('threads.bookmark');
        Route::post('threads/{thread}/follow', [FollowController::class, 'toggle'])->middleware('throttle:30,1')->name('threads.follow');

        Route::post('threads/{thread}/report', [ReportController::class, 'thread'])->middleware('throttle:10,1')->name('threads.report');
        Route::post('posts/{post}/report', [ReportController::class, 'post'])->middleware('throttle:10,1')->name('posts.report');

        Route::get('me/threads', [ProfileController::class, 'threads'])->name('me.threads');
        Route::get('me/posts', [ProfileController::class, 'posts'])->name('me.posts');
        Route::get('me/bookmarks', [ProfileController::class, 'bookmarks'])->name('me.bookmarks');

        // moderator + admin tooling; each controller checks the role itself
        Route::prefix('moderation')->name('moderation.')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::patch('reports/{report}', [ReportController::class, 'update'])->name('reports.update');

            Route::post('threads/{thread}/pin', [ModerationController::class, 'pin'])->name('threads.pin');
            Route::post('threads/{thread}/lock', [ModerationController::class, 'lock'])->name('threads.lock');
            Route::post('threads/{thread}/hide', [ModerationController::class, 'hide'])->name('threads.hide');

            Route::get('stats', [StatsController::class, 'index'])->name('stats');

            Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
            Route::post('users/{user}/ban', [UserAdminController::class, 'ban'])->name('users.ban');
            Route::patch('users/{user}/role', [UserAdminController::class, 'role'])->name('users.role');

            Route::get('categories', [CategoryAdminController::class, 'index'])->name('categories.index');
            Route::post('categories', [CategoryAdminController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [CategoryAdminController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryAdminController::class, 'destroy'])->name('categories.destroy');
        });

        // super-admin panel; every controller here requires the admin role
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('threads', [AdminThreadController::class, 'index'])->name('threads.index');
            Route::delete('threads/{thread}', [AdminThreadController::class, 'destroy'])->withTrashed()->name('threads.destroy');
            Route::post('threads/{thread}/restore', [AdminThreadController::class, 'restore'])->withTrashed()->name('threads.restore');

            Route::get('posts', [AdminPostController::class, 'index'])->name('posts.index');
            Route::delete('posts/{post}', [AdminPostController::class, 'destroy'])->withTrashed()->name('posts.destroy');
            Route::post('posts/{post}/restore', [AdminPostController::class, 'restore'])->withTrashed()->name('posts.restore');

            Route::get('tags', [AdminTagController::class, 'index'])->name('tags.index');
            Route::post('tags', [AdminTagController::class, 'store'])->name('tags.store');
            Route::put('tags/{tag}', [AdminTagController::class, 'update'])->name('tags.update');
            Route::delete('tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');
        });
    });
});
