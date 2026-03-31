<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\AddChantierCommentRequest;
use App\Modules\CRM\Requests\AddChantierCostRequest;
use App\Modules\CRM\Requests\AddChantierDocumentRequest;
use App\Modules\CRM\Requests\AddChantierTimeEntryRequest;
use App\Modules\CRM\Requests\MoveChantierStageRequest;
use App\Modules\CRM\Requests\StoreChantierRequest;
use App\Modules\CRM\Requests\UpdateChantierRequest;
use App\Modules\CRM\Services\ChantierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChantierController extends Controller
{
    public function __construct(private ChantierService $chantierService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->listForOwner($user->id, $request->query('status'))
        );
    }

    public function store(StoreChantierRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->chantierService->findForOwner($user->id, $id));
    }

    public function update(UpdateChantierRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->chantierService->delete($user->id, $id);

        return response()->json(['message' => 'Chantier supprimé.']);
    }

    // --- Pipeline ---

    public function pipeline(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->chantierService->pipeline($user->id));
    }

    public function moveStage(MoveChantierStageRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->moveStage($user->id, $id, $request->validated()['stage'])
        );
    }

    // --- Documents ---

    public function addDocument(AddChantierDocumentRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->addDocument($user->id, $id, $request->validated()),
            201
        );
    }

    public function removeDocument(Request $request, int $chantierId, int $documentId): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->chantierService->removeDocument($user->id, $chantierId, $documentId);

        return response()->json(['message' => 'Document supprimé.']);
    }

    // --- Comments ---

    public function addComment(AddChantierCommentRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->addComment($user->id, $id, $request->validated()),
            201
        );
    }

    // --- Time entries ---

    public function addTimeEntry(AddChantierTimeEntryRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->addTimeEntry($user->id, $id, $request->validated()),
            201
        );
    }

    // --- Costs ---

    public function addCost(AddChantierCostRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->chantierService->addCost($user->id, $id, $request->validated()),
            201
        );
    }
}
