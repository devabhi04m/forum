<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Forum\Entities\Category;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Tag;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Entities\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// generates throwaway demo content; every record hangs off a user with a
// @dummy.forum email, so deleting those users cascades the rest away
class AdminDummyDataController extends Controller
{
    private const EMAIL_DOMAIN = 'dummy.forum';

    public function status(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-dummy-data'), 403);

        $userIds = $this->dummyUserIds();

        return response()->json([
            'data' => [
                'users' => $userIds->count(),
                'threads' => Thread::whereIn('user_id', $userIds)->count(),
                'posts' => Post::whereIn('user_id', $userIds)->count(),
                'categories' => Category::where('is_dummy', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-dummy-data'), 403);

        $data = $request->validate([
            'users' => ['nullable', 'integer', 'min:1', 'max:50'],
            'threads' => ['nullable', 'integer', 'min:1', 'max:100'],
            'posts' => ['nullable', 'integer', 'min:0', 'max:500'],
            'categories' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $userCount = $data['users'] ?? 5;
        $threadCount = $data['threads'] ?? 10;
        $postCount = $data['posts'] ?? 30;
        $categoryCount = $data['categories'] ?? 3;

        // threads need somewhere to live; if nothing exists yet, make sure we generate some
        if ($categoryCount === 0 && ! Category::where('is_active', true)->exists()) {
            abort(422, 'There are no categories yet - set the categories field to at least 1.');
        }

        $created = DB::transaction(function () use ($userCount, $threadCount, $postCount, $categoryCount) {
            $icons = ['💻', '🎮', '🎬', '🎵', '📚', '🏀', '✈️', '🍕', '🔧', '💬', '📣', '🌍', '🎨', '🔬'];

            Category::factory()
                ->count($categoryCount)
                ->state(fn () => [
                    'slug' => Str::slug(fake()->unique()->words(2, true)).'-'.Str::lower(Str::random(4)),
                    'icon' => $icons[array_rand($icons)],
                    'is_dummy' => true,
                ])
                ->create();

            $categoryIds = Category::where('is_active', true)->pluck('id');
            $tagIds = Tag::pluck('id');

            $users = User::factory()
                ->count($userCount)
                ->state(fn () => [
                    'email' => 'dummy_'.Str::random(10).'@'.self::EMAIL_DOMAIN,
                    'created_at' => now()->subMinutes(random_int(60, 43200)),
                ])
                ->create();

            $threads = collect();
            for ($i = 0; $i < $threadCount; $i++) {
                $when = now()->subMinutes(random_int(30, 40000));

                $thread = Thread::factory()->create([
                    'category_id' => $categoryIds->random(),
                    'user_id' => $users->random()->id,
                    'views_count' => random_int(0, 400),
                    'created_at' => $when,
                    'updated_at' => $when,
                ]);

                if ($tagIds->isNotEmpty()) {
                    $thread->tags()->attach($tagIds->random(min(random_int(0, 3), $tagIds->count())));
                }

                $threads->push($thread);
            }

            for ($i = 0; $i < $postCount; $i++) {
                $thread = $threads->random();
                $when = $thread->created_at->copy()->addMinutes(random_int(5, 10000));

                Post::factory()->create([
                    'thread_id' => $thread->id,
                    'user_id' => $users->random()->id,
                    'created_at' => $when,
                    'updated_at' => $when,
                ]);
            }

            // a few upvotes from the dummy crowd, then fix up the counters
            foreach ($threads as $thread) {
                $voters = $users->random(min(random_int(0, 4), $users->count()));
                foreach ($voters as $voter) {
                    Vote::create([
                        'user_id' => $voter->id,
                        'thread_id' => $thread->id,
                        'post_id' => null,
                        'vote' => random_int(1, 5) === 1 ? -1 : 1,
                    ]);
                }

                $lastPost = $thread->posts()->latest('created_at')->first();
                $thread->timestamps = false;
                $thread->forceFill([
                    'replies_count' => $thread->posts()->count(),
                    'likes_count' => (int) $thread->votes()->sum('vote'),
                    'last_post_id' => $lastPost?->id,
                    'last_post_at' => $lastPost?->created_at,
                ])->save();
            }

            return [
                'users' => $userCount,
                'threads' => $threadCount,
                'posts' => $postCount,
                'categories' => $categoryCount,
            ];
        });

        Cache::forget('forum.categories.tree');

        return response()->json(['data' => $created], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-dummy-data'), 403);

        $userIds = $this->dummyUserIds();

        $deleted = [
            'users' => $userIds->count(),
            'threads' => Thread::whereIn('user_id', $userIds)->count(),
            'posts' => Post::whereIn('user_id', $userIds)->count(),
            'categories' => 0,
        ];

        DB::transaction(function () use ($userIds, &$deleted) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $userIds)
                ->delete();

            // hard delete; threads, posts, votes and bookmarks cascade at the DB level
            User::whereIn('id', $userIds)->delete();

            // generated categories go too, unless a real member has threads in them
            Category::where('is_dummy', true)
                ->get()
                ->each(function (Category $category) use (&$deleted) {
                    if (! Thread::withTrashed()->where('category_id', $category->id)->exists()) {
                        $category->delete();
                        $deleted['categories']++;
                    }
                });
        });

        Cache::forget('forum.categories.tree');

        return response()->json(['data' => $deleted]);
    }

    private function dummyUserIds()
    {
        return User::where('email', 'like', '%@'.self::EMAIL_DOMAIN)->pluck('id');
    }
}
