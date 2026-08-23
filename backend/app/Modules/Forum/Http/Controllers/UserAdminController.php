<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $users = User::query()
            ->withCount(['forumThreads', 'forumPosts'])
            ->when($request->query('q'), fn ($query, $q) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($users->items())->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'banned_at' => $user->banned_at,
                'threads_count' => $user->forum_threads_count,
                'posts_count' => $user->forum_posts_count,
                'created_at' => $user->created_at,
            ]),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function ban(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($user->isAdmin(), 422, 'Admins cannot be banned.');

        $user->forceFill(['banned_at' => $user->isBanned() ? null : now()])->save();

        return response()->json(['data' => ['banned_at' => $user->banned_at]]);
    }

    public function role(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($user->isAdmin(), 422, 'Admin roles cannot be changed here.');

        $data = $request->validate([
            'role' => ['required', 'in:user,moderator,admin'],
        ]);

        $user->forceFill(['role' => $data['role']])->save();

        return response()->json(['data' => ['role' => $user->role]]);
    }
}
