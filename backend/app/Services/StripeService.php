<?php

namespace App\Services;

use App\Mail\PaymentReceiptMail;
use App\Mail\SubscriptionActivatedMail;
use App\Support\MailHelper;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionRoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (!empty($secret)) {
            Stripe::setApiKey($secret);
        }
    }

    // --- Checkout ---
    public function createCheckoutForSubscription(User $user, SubscriptionPlan $plan): Session
    {
        return DB::transaction(function () use ($user, $plan) {
            $subscription = Subscription::create([
                'user_id'    => $user->id,
                'plan_id'    => $plan->id,
                'status'     => 'pending',
                'starts_at'  => now()->toDateString(),
                'ends_at'    => now()->addDays($plan->duration_days)->toDateString(),
            ]);

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'user_id'         => $user->id,
                'amount'          => $plan->price,
                'currency'        => 'EUR',
                'status'          => 'pending',
            ]);

            $session = Session::create([
                'mode'                 => 'payment',
                'customer_email'       => $user->email,
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name'        => "PitStop Manager - {$plan->name}",
                            'description' => $plan->description ?? "Suscripción {$plan->name}",
                        ],
                        'unit_amount' => (int) round($plan->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => rtrim(config('app.frontend_url'), '/')
                    . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => rtrim(config('app.frontend_url'), '/')
                    . '/subscription/cancel',
                'metadata' => [
                    'subscription_id' => (string) $subscription->id,
                    'payment_id'      => (string) $payment->id,
                    'user_id'         => (string) $user->id,
                    'plan_id'         => (string) $plan->id,
                ],
            ]);

            $payment->update([
                'stripe_metadata' => [
                    'checkout_session_id' => $session->id,
                ],
            ]);

            return $session;
        });
    }

    public function confirmCheckoutSession(string $sessionId, User $user): array
    {
        if (empty(config('services.stripe.secret'))) {
            return ['status' => 'error', 'message' => 'Stripe no está configurado en el servidor.'];
        }

        try {
            $session = Session::retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::warning('Stripe session retrieve failed', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);

            return ['status' => 'error', 'message' => 'Sesión de pago no válida o caducada.'];
        }

        $ownerId = $session->metadata['user_id'] ?? $session->metadata->user_id ?? null;
        if ((string) $ownerId !== (string) $user->id) {
            return ['status' => 'error', 'message' => 'Esta sesión de pago no pertenece a tu cuenta.'];
        }

        if ($session->payment_status !== 'paid') {
            return [
                'status'  => 'pending',
                'message' => 'El pago aún no se ha completado.',
            ];
        }

        return $this->handleCheckoutCompleted($session);
    }

    // --- Webhook ---
    public function handleWebhookEvent(string $payload, ?string $sigHeader): array
    {
        $secret = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            Log::warning('Stripe webhook called but STRIPE_WEBHOOK_SECRET is not configured.');
            return ['status' => 'error', 'message' => 'Secreto del webhook no configurado.'];
        }

        $event = Webhook::constructEvent($payload, (string) $sigHeader, $secret);

        return match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event->data->object),
            default                      => ['status' => 'ignored', 'type' => $event->type],
        };
    }

    private function handleCheckoutCompleted(object $session): array
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        $paymentId      = $session->metadata->payment_id ?? null;

        if (!$subscriptionId || !$paymentId) {
            Log::warning('Stripe checkout.session.completed missing metadata', [
                'session_id' => $session->id ?? null,
            ]);
            return ['status' => 'error', 'message' => 'Metadatos de pago incompletos.'];
        }

        $subscription = Subscription::with(['plan', 'user'])->find($subscriptionId);
        $payment      = Payment::find($paymentId);

        if (!$subscription || !$payment) {
            return ['status' => 'error', 'message' => 'Suscripción o pago no encontrado.'];
        }

        if ($subscription->status === 'active' && $payment->status === 'succeeded') {
            return ['status' => 'ok', 'message' => 'Ya procesado anteriormente.'];
        }

        DB::transaction(function () use ($subscription, $payment, $session) {
            $plan = $subscription->plan;

            $subscription->update([
                'status'                   => 'active',
                'starts_at'                => now()->toDateString(),
                'ends_at'                  => now()->addDays($plan->duration_days)->toDateString(),
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'reminder_week_sent_at'    => null,
                'reminder_day_sent_at'     => null,
            ]);

            $payment->update([
                'status'            => 'succeeded',
                'stripe_payment_id' => $session->payment_intent ?? null,
                'paid_at'           => now(),
                'stripe_metadata'   => array_merge((array) $payment->stripe_metadata, [
                    'checkout_session_id' => $session->id ?? null,
                    'payment_intent'      => $session->payment_intent ?? null,
                ]),
            ]);

            $user = $subscription->user;
            if ($user) {
                app(SubscriptionRoleService::class)->promoteToOrganizerIfNeeded($user);
                Log::info('User role synced after subscription activation', [
                    'user_id'         => $user->id,
                    'subscription_id' => $subscription->id,
                    'role'            => $user->fresh()->role,
                ]);
            }
        });

        $subscription->refresh()->load(['plan', 'user']);
        $payment->refresh()->load(['subscription.plan', 'user']);

        if ($subscription->user?->email) {
            MailHelper::sendSafely(
                $subscription->user->email,
                new SubscriptionActivatedMail($subscription),
                ['subscription_id' => $subscription->id, 'type' => 'subscription_activated'],
            );
            MailHelper::sendSafely(
                $subscription->user->email,
                new PaymentReceiptMail($payment),
                ['payment_id' => $payment->id, 'type' => 'payment_receipt'],
            );
        }

        return ['status' => 'success'];
    }

    private function handleCheckoutExpired(object $session): array
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        $paymentId      = $session->metadata->payment_id ?? null;

        if ($paymentId) {
            Payment::where('id', $paymentId)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }
        if ($subscriptionId) {
            Subscription::where('id', $subscriptionId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        return ['status' => 'ok', 'message' => 'Sesión de pago caducada.'];
    }
}
