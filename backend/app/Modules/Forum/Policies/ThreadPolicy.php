<?php

namespace App\Modules\Forum\Policies;

use App\Models\User;
use App\Modules\Forum\Entities\Thread;

class ThreadPolicy
{
    // admins can do anything
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Thread $thread): bool
    {
        return $user->isModerator() || ($user->id === $thread->user_id && ! $thread->is_locked);
    }

    public function delete(User $user, Thread $thread): bool
    {
        return $user->isModerator() || $user->id === $thread->user_id;
    }

    public function reply(User $user, Thread $thread): bool
    {
        return $user->isModerator() || ! $thread->is_locked;
    }

    public function moderate(User $user): bool
    {
        return $user->isModerator();
    }
}
