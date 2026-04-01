<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Requests\ApplyJobRequest;
use App\Modules\Ecosystem\Requests\StoreJobRequest;
use App\Modules\Ecosystem\Requests\UpdateJobRequest;
use App\Modules\Ecosystem\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(private JobService $jobService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->jobService->listActive(
                $request->query('category'),
                $request->query('city'),
                $request->query('contract_type'),
            )
        );
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->jobService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->jobService->find($id));
    }

    public function update(UpdateJobRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->jobService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->jobService->delete($user->id, $id);

        return response()->json(['message' => 'Offre supprimée.']);
    }

    public function apply(ApplyJobRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->jobService->apply($user->id, $id, $request->validated()['message'] ?? null),
            201
        );
    }

    public function applications(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->jobService->listApplications($user->id, $id));
    }
}
