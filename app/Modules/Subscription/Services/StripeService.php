<?php

namespace App\Modules\Subscription\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer as StripeCustomer;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    // ===== STATUS =====

    public function getStatus(int $userId): array
    {
        $subscription = Subscription::where('user_id', $userId)
            ->latest()
            ->first();

        if (!$subscription) {
            return [
                'is_active'          => false,
                'plan'               => null,
                'status'             => null,
                'current_period_end' => null,
            ];
        }

        return [
            'is_active'          => $subscription->isActive(),
            'plan'               => $subscription->plan,
            'status'             => $subscription->status,
            'current_period_end' => $subscription->current_period_end,
        ];
    }

    // ===== CHECKOUT =====

    /**
     * Create a Stripe Checkout Session.
     * Creates a Stripe Customer if one doesn't exist.
     */
    public function createCheckoutSession(int $userId, string $plan): array
    {
        $user = User::findOrFail($userId);

        // Get or create Stripe Customer
        $subscription = Subscription::where('user_id', $userId)->latest()->first();
        $customerId = $subscription?->stripe_customer_id;

        if (!$customerId) {
            $customer = StripeCustomer::create([
                'email'    => $user->email,
                'name'     => $user->display_name ?? $user->username,
                'metadata' => ['user_id' => $userId, 'core_uuid' => $user->core_uuid],
            ]);
            $customerId = $customer->id;
        }

        $priceId = config("stripe.prices.{$plan}");
        if (!$priceId) {
            abort(422, "Plan invalide : {$plan}");
        }

        $session = StripeSession::create([
            'customer'               => $customerId,
            'payment_method_types'   => ['card'],
            'mode'                   => 'subscription',
            'line_items'             => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'success_url'            => config('app.url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'             => config('app.url') . '/subscription/cancel',
            'metadata'               => [
                'user_id' => $userId,
                'plan'    => $plan,
            ],
        ]);

        // Store customer_id for future use
        if (!$subscription) {
            Subscription::create([
                'user_id'            => $userId,
                'stripe_customer_id' => $customerId,
                'plan'               => $plan,
                'status'             => 'pending',
            ]);
        }

        return [
            'checkout_url' => $session->url,
            'session_id'   => $session->id,
        ];
    }

    // ===== CANCEL =====

    public function cancel(int $userId): array
    {
        $subscription = Subscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();

        if (!$subscription || !$subscription->stripe_subscription_id) {
            abort(422, 'Aucun abonnement actif à annuler.');
        }

        $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
        $stripeSubscription->cancel();

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        User::where('id', $userId)->update(['has_pro_subscription' => false]);

        return ['message' => 'Abonnement annulé.', 'status' => 'cancelled'];
    }

    // ===== CUSTOMER PORTAL =====

    public function getPortalUrl(int $userId): array
    {
        $subscription = Subscription::where('user_id', $userId)->latest()->first();

        if (!$subscription || !$subscription->stripe_customer_id) {
            abort(422, 'Aucun compte Stripe associé.');
        }

        $session = PortalSession::create([
            'customer'   => $subscription->stripe_customer_id,
            'return_url' => config('app.url') . '/subscription',
        ]);

        return ['portal_url' => $session->url];
    }

    // ===== WEBHOOK =====

    /**
     * Handle incoming Stripe webhook events.
     * Verifies signature with STRIPE_WEBHOOK_SECRET.
     */
    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $webhookSecret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            abort(400, 'Invalid signature.');
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            default:
                Log::info('Stripe webhook unhandled event', ['type' => $event->type]);
        }
    }

    // ===== WEBHOOK HANDLERS =====

    private function handleCheckoutCompleted(object $session): void
    {
        $userId = $session->metadata->user_id ?? null;
        $plan = $session->metadata->plan ?? 'pro_monthly';

        if (!$userId) {
            Log::warning('Stripe checkout completed without user_id in metadata');
            return;
        }

        $subscription = Subscription::where('user_id', $userId)->latest()->first();

        if ($subscription) {
            $subscription->update([
                'stripe_customer_id'     => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'plan'                   => $plan,
                'status'                 => 'active',
                'current_period_start'   => now(),
            ]);
        } else {
            Subscription::create([
                'user_id'                => $userId,
                'stripe_customer_id'     => $session->customer,
                'stripe_subscription_id' => $session->subscription,
                'plan'                   => $plan,
                'status'                 => 'active',
                'current_period_start'   => now(),
            ]);
        }

        User::where('id', $userId)->update(['has_pro_subscription' => true]);
    }

    private function handleSubscriptionUpdated(object $stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status'               => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end'   => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);

        $isActive = in_array($stripeSubscription->status, ['active', 'trialing']);
        User::where('id', $subscription->user_id)->update(['has_pro_subscription' => $isActive]);
    }

    private function handleSubscriptionDeleted(object $stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        User::where('id', $subscription->user_id)->update(['has_pro_subscription' => false]);
    }
}
