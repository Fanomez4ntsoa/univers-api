<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\StoreProspectRequest;
use App\Modules\CRM\Requests\UpdateProspectRequest;
use App\Modules\CRM\Services\ClientService;
use App\Modules\CRM\Services\ProspectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function __construct(
        private ProspectService $prospectService,
        private ClientService $clientService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $prospects = $this->prospectService->listForOwner($user->id);

        return response()->json($prospects);
    }

    public function store(StoreProspectRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $prospect = $this->prospectService->create($user->id, $request->validated());

        return response()->json($prospect, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $prospect = $this->prospectService->findForOwner($user->id, $id);

        return response()->json($prospect);
    }

    public function update(UpdateProspectRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $prospect = $this->prospectService->update($user->id, $id, $request->validated());

        return response()->json($prospect);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->prospectService->delete($user->id, $id);

        return response()->json(['message' => 'Prospect supprimé.']);
    }

    public function convertToClient(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $client = $this->clientService->convertFromProspect($user->id, $id);

        return response()->json($client, 201);
    }
}
