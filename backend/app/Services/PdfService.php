<?php
namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generatePaymentReceipt(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['user', 'subscription.plan']);

        return Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment,
            'user' => $payment->user,
            'plan' => $payment->subscription->plan,
        ]);
    }
}
