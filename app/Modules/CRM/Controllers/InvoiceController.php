<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Requests\StoreInvoiceRequest;
use App\Modules\CRM\Requests\UpdateInvoiceRequest;
use App\Modules\CRM\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->invoiceService->listForOwner(
                $user->id,
                $request->query('status'),
                $request->query('client_id') ? (int) $request->query('client_id') : null,
            )
        );
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->invoiceService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->invoiceService->findForOwner($user->id, $id));
    }

    public function update(UpdateInvoiceRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->invoiceService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->invoiceService->delete($user->id, $id);

        return response()->json(['message' => 'Facture supprimée.']);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->invoiceService->send($user->id, $id));
    }

    public function markPaid(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->invoiceService->markPaid($user->id, $id));
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->invoiceService->cancel($user->id, $id));
    }
}
