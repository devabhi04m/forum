<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Entities\Post;
use App\Modules\Forum\Entities\Thread;
use App\Modules\Forum\Entities\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function thread(Request $request, Thread $thread): JsonResponse
    {
        return $this->applyVote($request, $thread, ['thread_id' => $thread->id, 'post_id' => null]);
    }

    public function post(Request $request, Post $post): JsonResponse
    {
        return $this->applyVote($request, $post, ['thread_id' => null, 'post_id' => $post->id]);
    }

    private function applyVote(Request $request, Thread|Post $target, array $keys): JsonResponse
    {
        $data = $request->validate([
            'vote' => ['required', 'integer', 'in:-1,0,1'],
        ]);

        $vote = (int) $data['vote'];
        $keys['user_id'] = $request->user()->id;

        $score = DB::transaction(function () use ($keys, $vote, $target) {
            if ($vote === 0) {
                Vote::where($keys)->delete();
            } else {
                Vote::updateOrCreate($keys, ['vote' => $vote]);
            }

            // recount instead of incrementing, cheaper to reason about and self-healing
            $column = $target instanceof Thread ? 'thread_id' : 'post_id';
            $score = (int) Vote::where($column, $target->id)->sum('vote');

            $target->timestamps = false;
            $target->forceFill(['likes_count' => $score])->save();

            return $score;
        });

        return response()->json([
            'data' => [
                'score' => $score,
                'my_vote' => $vote,
            ],
        ]);
    }
}
