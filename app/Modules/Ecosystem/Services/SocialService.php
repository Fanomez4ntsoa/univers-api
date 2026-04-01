<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SocialService
{
    private const PROFILE_FIELDS = ['id', 'username', 'display_name', 'avatar_url', 'metier', 'city', 'company_name', 'bio', 'user_type', 'is_verified', 'followers_count', 'following_count', 'posts_count'];

    // ===== DISCOVER =====

    /**
     * Discover artisans — paginated list with is_following flag.
     * Mirrors Emergent GET /search (type=users).
     */
    public function discoverUsers(int $currentUserId, int $page = 1): LengthAwarePaginator
    {
        $paginated = User::where('is_active', true)
            ->select(self::PROFILE_FIELDS)
            ->orderByDesc('followers_count')
            ->paginate(20, ['*'], 'page', $page);

        // Add is_following flag
        $followingIds = UserFollow::where('follower_id', $currentUserId)
            ->pluck('following_id')
            ->toArray();

        $paginated->getCollection()->transform(function ($user) use ($followingIds, $currentUserId) {
            $user->is_following = in_array($user->id, $followingIds);
            $user->is_self = $user->id === $currentUserId;
            return $user;
        });

        return $paginated;
    }

    // ===== PROFIL PUBLIC =====

    /**
     * Public profile with stats + 5 latest posts.
     */
    public function showProfile(int $userId): array
    {
        $user = User::select(self::PROFILE_FIELDS)
            ->findOrFail($userId);

        $latestPosts = Post::where('user_id', $userId)
            ->where('visibility', 'public')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'user'  => $user,
            'posts' => $latestPosts,
        ];
    }

    // ===== MON PROFIL =====

    /**
     * My profile with stats.
     */
    public function myProfile(int $userId): array
    {
        $user = User::select(self::PROFILE_FIELDS)
            ->findOrFail($userId);

        return [
            'user'            => $user,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'posts_count'     => $user->posts_count,
        ];
    }

    // ===== FOLLOW/UNFOLLOW =====

    /**
     * Toggle follow. Returns { following: bool, followers_count: int }.
     */
    public function toggleFollow(int $followerId, int $followingId): array
    {
        if ($followerId === $followingId) {
            abort(422, 'Vous ne pouvez pas vous suivre vous-même.');
        }

        User::findOrFail($followingId);

        $existing = UserFollow::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->first();

        if ($existing) {
            $existing->delete();
            User::where('id', $followingId)->decrement('followers_count');
            User::where('id', $followerId)->decrement('following_count');

            return [
                'following'       => false,
                'followers_count' => User::find($followingId)->followers_count,
            ];
        }

        UserFollow::create([
            'follower_id'  => $followerId,
            'following_id' => $followingId,
        ]);
        User::where('id', $followingId)->increment('followers_count');
        User::where('id', $followerId)->increment('following_count');

        return [
            'following'       => true,
            'followers_count' => User::find($followingId)->followers_count,
        ];
    }

    // ===== FOLLOWERS / FOLLOWING =====

    public function listFollowers(int $userId): Collection
    {
        $followerIds = UserFollow::where('following_id', $userId)
            ->pluck('follower_id');

        return User::whereIn('id', $followerIds)
            ->select(['id', 'username', 'display_name', 'avatar_url', 'metier', 'city'])
            ->get();
    }

    public function listFollowing(int $userId): Collection
    {
        $followingIds = UserFollow::where('follower_id', $userId)
            ->pluck('following_id');

        return User::whereIn('id', $followingIds)
            ->select(['id', 'username', 'display_name', 'avatar_url', 'metier', 'city'])
            ->get();
    }

    // ===== FEED PERSONNALISÉ =====

    /**
     * Personalized feed — posts from followed users, 15/page.
     * Mirrors Emergent GET /feed logic.
     * Returns empty [] if no followings.
     */
    public function personalizedFeed(int $userId, int $page = 1): LengthAwarePaginator
    {
        $followingIds = UserFollow::where('follower_id', $userId)
            ->pluck('following_id')
            ->toArray();

        if (empty($followingIds)) {
            return Post::where('id', 0)->paginate(15); // empty paginator
        }

        return Post::whereIn('user_id', $followingIds)
            ->where('visibility', 'public')
            ->with('user:id,username,display_name,avatar_url')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'page', $page);
    }
}
