<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Models\Circuit;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Race;
use App\Models\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'users'          => User::count(),
                'organizers'     => User::where('role', 'organizer')->count(),
                'pilots'         => User::where('role', 'pilot')->count(),
                'championships'  => Championship::count(),
                'circuits'       => Circuit::count(),
                'races'          => Race::count(),
                'inscriptions'   => Inscription::count(),
                'subscriptions'  => Subscription::where('status', 'active')->count(),
                'payments_month' => Payment::where('status', 'succeeded')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('amount'),
            ],
            'recentChampionships' => Championship::with(['category', 'user'])
                ->latest()->limit(5)->get(),
            'recentPayments' => Payment::with(['user', 'subscription.plan'])
                ->latest()->limit(5)->get(),
        ]);
    }
}
