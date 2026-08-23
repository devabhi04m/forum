<?php

namespace App\Modules\Forum\Entities;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    protected $table = 'forum_tags';

    protected $fillable = ['name', 'slug'];

    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'forum_thread_tag', 'tag_id', 'thread_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}
