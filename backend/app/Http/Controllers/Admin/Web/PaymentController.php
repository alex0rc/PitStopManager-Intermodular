<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'subscription.plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function create()
    {
        return view('admin.payments.form', [
            'payment'       => new Payment(['currency' => 'EUR', 'status' => 'pending']),
            'users'         => User::orderBy('name')->get(),
            'subscriptions' => Subscription::with(['user', 'plan'])->latest()->limit(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($data['status'] === 'succeeded' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        Payment::create($data);

        return redirect()->route('admin.payments.index')->with('success', 'Pago creado.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'subscription.plan']);

        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('admin.payments.form', [
            'payment'       => $payment,
            'users'         => User::orderBy('name')->get(),
            'subscriptions' => Subscription::with(['user', 'plan'])->latest()->limit(100)->get(),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $this->validated($request, $payment);
        if ($data['status'] === 'succeeded' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        $payment->update($data);

        return redirect()->route('admin.payments.index')->with('success', 'Pago actualizado.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Pago eliminado.');
    }

    private function validated(Request $request, ?Payment $payment = null): array
    {
        $data = $request->validate([
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'user_id'         => ['required', 'exists:users,id'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'currency'        => ['required', 'string', 'max:3'],
            'status'          => ['required', Rule::in(['succeeded', 'pending', 'failed', 'refunded'])],
            'paid_at'         => ['nullable', 'date'],
        ]);

        $subscription = Subscription::find($data['subscription_id']);
        if ($subscription && (int) $subscription->user_id !== (int) $data['user_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => 'El usuario debe coincidir con el de la suscripción.',
            ]);
        }

        return $data;
    }
}
