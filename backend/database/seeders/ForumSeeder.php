<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Tag;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(6)->create();

        $tags = Tag::factory()->count(8)->create();

        $programming = Category::factory()->create([
            'name' => 'Programming',
            'slug' => 'programming',
            'sort_order' => 1,
        ]);

        $laravel = Category::factory()->create([
            'parent_id' => $programming->id,
            'name' => 'Laravel',
            'slug' => 'laravel',
            'sort_order' => 1,
        ]);

        $vue = Category::factory()->create([
            'parent_id' => $programming->id,
            'name' => 'Vue',
            'slug' => 'vue',
            'sort_order' => 2,
        ]);

        $general = Category::factory()->create([
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 2,
        ]);

        foreach ([$laravel, $vue, $general] as $category) {
            Thread::factory()
                ->count(5)
                ->create([
                    'category_id' => $category->id,
                    'user_id' => fn () => $users->random()->id,
                ])
                ->each(function (Thread $thread) use ($users, $tags) {
                    $thread->tags()->attach($tags->random(rand(1, 3))->pluck('id'));

                    $replies = Post::factory()
                        ->count(rand(2, 6))
                        ->create([
                            'thread_id' => $thread->id,
                            'user_id' => fn () => $users->random()->id,
                        ]);

                    $lastPost = $replies->sortByDesc('created_at')->first();

                    $thread->update([
                        'replies_count' => $replies->count(),
                        'last_post_id' => $lastPost?->id,
                        'last_post_at' => $lastPost?->created_at,
                    ]);
                });
        }
    }
}
