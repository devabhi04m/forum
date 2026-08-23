<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'api';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // roles and permissions live in spatie/laravel-permission; the old `role`
    // column sticks around as a denormalized display value kept in sync on change
    public function isModerator(): bool
    {
        return $this->can('moderate-threads');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(Thread::class, 'user_id');
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function bookmarkedThreads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'forum_bookmarks', 'user_id', 'thread_id')->withTimestamps();
    }

    public function followedThreads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'forum_followers', 'user_id', 'thread_id')->withTimestamps();
    }
}
