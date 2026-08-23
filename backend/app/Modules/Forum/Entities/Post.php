<?php

namespace App\Modules\Forum\Entities;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'forum_posts';

    protected $fillable = [
        'thread_id', 'user_id', 'parent_id', 'content', 'is_solution',
    ];

    protected function casts(): array
    {
        return [
            'is_solution' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'post_id');
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
