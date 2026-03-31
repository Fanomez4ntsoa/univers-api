<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\AddClientNoteRequest;
use App\Modules\CRM\Requests\StoreClientRequest;
use App\Modules\CRM\Requests\UpdateClientRequest;
use App\Modules\CRM\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private ClientService $clientService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->clientService->listForOwner($user->id));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $client = $this->clientService->create($user->id, $request->validated());

        return response()->json($client, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->clientService->showEnriched($user->id, $id));
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $client = $this->clientService->update($user->id, $id, $request->validated());

        return response()->json($client);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->clientService->delete($user->id, $id);

        return response()->json(['message' => 'Client supprimé.']);
    }

    public function addNote(AddClientNoteRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $note = $this->clientService->addNote($user->id, $id, $request->validated());

        return response()->json($note, 201);
    }

    public function generatePortalToken(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $client = $this->clientService->generatePortalToken($user->id, $id);

        return response()->json([
            'token'      => $client->portal_token,
            'created_at' => $client->portal_token_created_at,
            'message'    => 'Lien d\'accès généré.',
        ]);
    }
}
