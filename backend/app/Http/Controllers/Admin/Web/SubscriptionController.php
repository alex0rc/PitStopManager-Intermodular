<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionRoleService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionRoleService $roleService) {}

    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $subscriptions = $query->latest()->paginate(20)->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        return view('admin.subscriptions.form', [
            'subscription' => new Subscription([
                'status'    => 'pending',
                'starts_at' => now()->toDateString(),
                'ends_at'   => now()->addDays(30)->toDateString(),
            ]),
            'users' => User::orderBy('name')->get(),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $subscription = Subscription::create($data);
        $this->syncUserRole($subscription);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Suscripción creada.');
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'plan', 'payments']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        return view('admin.subscriptions.form', [
            'subscription' => $subscription,
            'users'          => User::orderBy('name')->get(),
            'plans'          => SubscriptionPlan::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $this->validated($request);
        $subscription->update($data);
        $this->syncUserRole($subscription->fresh());

        return redirect()->route('admin.subscriptions.index')->with('success', 'Suscripción actualizada.');
    }

    public function destroy(Subscription $subscription)
    {
        $user = $subscription->user;
        $subscription->delete();

        if ($user) {
            $this->roleService->syncRoleForUser($user);
        }

        return redirect()->route('admin.subscriptions.index')->with('success', 'Suscripción eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id'   => ['required', 'exists:users,id'],
            'plan_id'   => ['required', 'exists:subscription_plans,id'],
            'status'    => ['required', 'in:pending,active,expired,cancelled'],
            'starts_at' => ['required', 'date'],
            'ends_at'   => ['required', 'date', 'after_or_equal:starts_at'],
        ]);
    }

    private function syncUserRole(Subscription $subscription): void
    {
        $subscription->load('user');
        if ($subscription->user) {
            $this->roleService->syncRoleForUser($subscription->user);
        }
    }
}
