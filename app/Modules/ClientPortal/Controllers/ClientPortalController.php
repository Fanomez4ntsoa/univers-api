<?php

namespace App\Modules\ClientPortal\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ClientPortal\Requests\SignQuotePortalRequest;
use App\Modules\ClientPortal\Services\ClientPortalService;
use Illuminate\Http\JsonResponse;

class ClientPortalController extends Controller
{
    public function __construct(private ClientPortalService $portalService) {}

    public function dashboard(string $token): JsonResponse
    {
        return response()->json($this->portalService->dashboard($token));
    }

    public function listQuotes(string $token): JsonResponse
    {
        return response()->json($this->portalService->listQuotes($token));
    }

    public function showQuote(string $token, int $id): JsonResponse
    {
        return response()->json($this->portalService->showQuote($token, $id));
    }

    public function signQuote(SignQuotePortalRequest $request, string $token, int $id): JsonResponse
    {
        $quote = $this->portalService->signQuote($token, $id, $request->validated());

        return response()->json([
            'message' => 'Devis signé avec succès.',
            'quote'   => $quote,
        ]);
    }

    public function listInvoices(string $token): JsonResponse
    {
        return response()->json($this->portalService->listInvoices($token));
    }

    public function showInvoice(string $token, int $id): JsonResponse
    {
        return response()->json($this->portalService->showInvoice($token, $id));
    }
}
