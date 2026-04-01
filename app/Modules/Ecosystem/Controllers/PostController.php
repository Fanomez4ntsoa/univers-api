<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Requests\AddPostCommentRequest;
use App\Modules\Ecosystem\Requests\StorePostRequest;
use App\Modules\Ecosystem\Requests\UpdatePostRequest;
use App\Modules\Ecosystem\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private PostService $postService) {}

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);

        return response()->json($this->postService->feed($page));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->postService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->postService->find($id));
    }

    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->postService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->postService->delete($user->id, $id);

        return response()->json(['message' => 'Post supprimé.']);
    }

    public function toggleLike(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->postService->toggleLike($user->id, $id));
    }

    public function addComment(AddPostCommentRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->postService->addComment($user->id, $id, $request->validated()['content']),
            201
        );
    }

    public function listComments(int $id): JsonResponse
    {
        return response()->json($this->postService->listComments($id));
    }
}
