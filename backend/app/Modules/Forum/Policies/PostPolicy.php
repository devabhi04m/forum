<?php

namespace App\Modules\Forum\Policies;

use App\Models\User;
use App\Modules\Forum\Entities\Post;

class PostPolicy
{
    // admins can do anything
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isModerator() || $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isModerator() || $user->id === $post->user_id;
    }
}
