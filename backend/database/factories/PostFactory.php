<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => $this->faker->paragraphs(2, true),
            'is_solution' => false,
        ];
    }
}
