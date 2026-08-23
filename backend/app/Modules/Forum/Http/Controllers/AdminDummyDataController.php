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
        abort_unless($request->user()->isAdmin(), 403);

        $userIds = $this->dummyUserIds();

        return response()->json([
            'data' => [
                'users' => $userIds->count(),
                'threads' => Thread::whereIn('user_id', $userIds)->count(),
                'posts' => Post::whereIn('user_id', $userIds)->count(),
                'has_categories' => Category::where('is_active', true)->exists(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'users' => ['nullable', 'integer', 'min:1', 'max:50'],
            'threads' => ['nullable', 'integer', 'min:1', 'max:100'],
            'posts' => ['nullable', 'integer', 'min:0', 'max:500'],
        ]);

        $categoryIds = Category::where('is_active', true)->pluck('id');
        abort_if($categoryIds->isEmpty(), 422, 'Create at least one active category first.');

        $userCount = $data['users'] ?? 5;
        $threadCount = $data['threads'] ?? 10;
        $postCount = $data['posts'] ?? 30;

        $created = DB::transaction(function () use ($categoryIds, $userCount, $threadCount, $postCount) {
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

            return ['users' => $userCount, 'threads' => $threadCount, 'posts' => $postCount];
        });

        Cache::forget('forum.categories.tree');

        return response()->json(['data' => $created], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $userIds = $this->dummyUserIds();

        $deleted = [
            'users' => $userIds->count(),
            'threads' => Thread::whereIn('user_id', $userIds)->count(),
            'posts' => Post::whereIn('user_id', $userIds)->count(),
        ];

        DB::transaction(function () use ($userIds) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $userIds)
                ->delete();

            // hard delete; threads, posts, votes and bookmarks cascade at the DB level
            User::whereIn('id', $userIds)->delete();
        });

        Cache::forget('forum.categories.tree');

        return response()->json(['data' => $deleted]);
    }

    private function dummyUserIds()
    {
        return User::where('email', 'like', '%@'.self::EMAIL_DOMAIN)->pluck('id');
    }
}
