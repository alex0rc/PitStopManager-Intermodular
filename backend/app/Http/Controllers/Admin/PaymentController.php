<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['subscription.plan', 'user'])
            ->latest()
            ->paginate(15);
        return PaymentResource::collection($payments);
    }
}
