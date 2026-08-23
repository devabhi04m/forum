<?php

namespace App\Modules\Forum\Entities;

use App\Models\User;
use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    /** @use HasFactory<ThreadFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'forum_threads';

    protected $fillable = [
        'category_id', 'user_id', 'title', 'slug', 'content', 'status',
        'is_pinned', 'is_locked', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'is_featured' => 'boolean',
            'last_post_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'thread_id');
    }

    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'last_post_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'forum_thread_tag', 'thread_id', 'tag_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'thread_id');
    }

    public function bookmarkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_bookmarks', 'thread_id', 'user_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_followers', 'thread_id', 'user_id')->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory(): ThreadFactory
    {
        return ThreadFactory::new();
    }
}
