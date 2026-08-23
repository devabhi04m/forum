<?php

namespace App\Modules\Forum\Entities;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $table = 'forum_categories';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class, 'category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
