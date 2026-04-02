<?php

namespace App\Modules\Subscription\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Subscription\Requests\CheckoutRequest;
use App\Modules\Subscription\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private StripeService $stripeService) {}

    public function status(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->stripeService->getStatus($user->id));
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->stripeService->createCheckoutSession($user->id, $request->validated()['plan'])
        );
    }

    public function cancel(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->stripeService->cancel($user->id));
    }

    public function portal(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->stripeService->getPortalUrl($user->id));
    }

    public function webhook(Request $request): JsonResponse
    {
        $this->stripeService->handleWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature', '')
        );

        return response()->json(['received' => true]);
    }
}
