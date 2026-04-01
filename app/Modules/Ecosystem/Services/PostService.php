<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Feed paginé — tous les posts publics, 15 par page.
     * Enrichi avec l'auteur (username, display_name, avatar).
     * Mirrors Emergent GET /feed/discover (all public posts, newest first).
     */
    public function feed(int $page = 1): LengthAwarePaginator
    {
        return Post::where('visibility', 'public')
            ->with('user:id,username,display_name,avatar_url')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'page', $page);
    }

    public function create(int $userId, array $data): Post
    {
        $data['user_id'] = $userId;

        return Post::create($data)->load('user:id,username,display_name,avatar_url');
    }

    public function find(int $id): Post
    {
        return Post::with('user:id,username,display_name,avatar_url')
            ->findOrFail($id);
    }

    public function update(int $userId, int $id, array $data): Post
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres posts.');
        }

        $post->update($data);

        return $post->fresh()->load('user:id,username,display_name,avatar_url');
    }

    public function delete(int $userId, int $id): void
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $userId) {
            abort(403, 'Vous ne pouvez supprimer que vos propres posts.');
        }

        $post->delete();
    }

    /**
     * Like toggle — si déjà liké → unlike, sinon → like.
     * Retourne l'état actuel { liked: bool, likes_count: int }.
     */
    public function toggleLike(int $userId, int $postId): array
    {
        $post = Post::findOrFail($postId);

        $existingLike = PostLike::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('likes_count');

            return ['liked' => false, 'likes_count' => $post->fresh()->likes_count];
        }

        PostLike::create([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
        $post->increment('likes_count');

        return ['liked' => true, 'likes_count' => $post->fresh()->likes_count];
    }

    public function addComment(int $userId, int $postId, string $content): PostComment
    {
        Post::findOrFail($postId)->increment('comments_count');

        return PostComment::create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
        ])->load('user:id,username,display_name,avatar_url');
    }

    public function listComments(int $postId): \Illuminate\Database\Eloquent\Collection
    {
        Post::findOrFail($postId);

        return PostComment::where('post_id', $postId)
            ->with('user:id,username,display_name,avatar_url')
            ->orderByDesc('created_at')
            ->get();
    }
}
