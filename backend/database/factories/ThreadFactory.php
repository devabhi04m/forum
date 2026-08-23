<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'content' => $this->faker->paragraphs(3, true),
            'status' => 'published',
            'is_pinned' => false,
            'is_locked' => false,
            'is_featured' => false,
        ];
    }
}
