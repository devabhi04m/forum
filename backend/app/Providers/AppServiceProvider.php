<?php

namespace App\Providers;

use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Policies\PostPolicy;
use App\Modules\Forum\Policies\ThreadPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Thread::class, ThreadPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);

        Passport::enablePasswordGrant();
    }
}
