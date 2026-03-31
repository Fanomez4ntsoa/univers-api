<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\SignQuoteRequest;
use App\Modules\CRM\Requests\StoreQuoteRequest;
use App\Modules\CRM\Requests\UpdateQuoteRequest;
use App\Modules\CRM\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(private QuoteService $quoteService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->quoteService->listForOwner(
                $user->id,
                $request->query('status'),
                $request->query('client_id') ? (int) $request->query('client_id') : null,
            )
        );
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->quoteService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->quoteService->findForOwner($user->id, $id));
    }

    public function update(UpdateQuoteRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->quoteService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->quoteService->delete($user->id, $id);

        return response()->json(['message' => 'Devis supprimé.']);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->quoteService->send($user->id, $id));
    }

    public function sign(SignQuoteRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $data = $request->validated();
        $data['signed_ip'] = $request->ip();

        return response()->json($this->quoteService->sign($user->id, $id, $data));
    }

    public function duplicate(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->quoteService->duplicate($user->id, $id),
            201
        );
    }

    public function convertToInvoice(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->quoteService->convertToInvoice($user->id, $id),
            201
        );
    }
}
