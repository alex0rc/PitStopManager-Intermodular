<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\PdfService;
use App\Services\StripeService;
use App\Services\SubscriptionQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function store(
        StoreSubscriptionRequest $request,
        StripeService $stripe
    ): JsonResponse {
        $plan = SubscriptionPlan::where('is_active', true)
            ->findOrFail($request->validated('plan_id'));

        if (empty(config('services.stripe.secret'))) {
            return response()->json([
                'message' => 'Stripe no está configurado en el servidor.',
            ], 503);
        }

        try {
            $session = $stripe->createCheckoutForSubscription($request->user(), $plan);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            report($e);

            $detail = $e->getMessage();
            $message = config('app.debug')
                ? "No se pudo crear la sesión de pago: {$detail}"
                : 'No se pudo crear la sesión de pago.';

            return response()->json([
                'message' => $message,
                'error'   => $detail,
            ], 502);
        }

        return response()->json([
            'checkout_url' => $session->url,
            'session_id'   => $session->id,
        ]);
    }

    public function confirmCheckout(Request $request, StripeService $stripe): JsonResponse
    {
        $request->validate([
            'session_id' => ['required', 'string', 'max:255'],
        ]);

        if (empty(config('services.stripe.secret'))) {
            return response()->json([
                'message' => 'Stripe no está configurado en el servidor.',
            ], 503);
        }

        $result = $stripe->confirmCheckoutSession(
            $request->input('session_id'),
            $request->user()
        );

        return match ($result['status']) {
            'success', 'ok' => response()->json([
                'message' => 'Suscripción activada correctamente.',
                'role'    => $request->user()->fresh()->role,
            ]),
            'pending' => response()->json([
                'message' => $result['message'] ?? 'El pago sigue en proceso.',
            ], 202),
            default => response()->json([
                'message' => $result['message'] ?? 'No se pudo confirmar el pago.',
            ], 422),
        };
    }

    public function mySubscription(Request $request, SubscriptionQuotaService $quota): JsonResponse
    {
        $subscription = Subscription::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->where('ends_at', '>=', now()->toDateString())
            ->with('plan')
            ->latest()
            ->first();

        return response()->json([
            'data' => $subscription ? new SubscriptionResource($subscription) : null,
            'quota' => $quota->summary($request->user()),
        ]);
    }

    public function myPayments(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('subscription.plan')
            ->latest()
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function downloadPdf(Request $request, Payment $payment, PdfService $pdfService): Response
    {
        if ($payment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $filename = 'comprobante-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdfService
            ->generatePaymentReceipt($payment)
            ->download($filename);
    }
}
