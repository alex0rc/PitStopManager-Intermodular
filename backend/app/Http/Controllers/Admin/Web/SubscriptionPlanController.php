<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => new SubscriptionPlan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $request->boolean('is_active', true);

        SubscriptionPlan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan creado.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validated($request, $plan);
        $data['is_active'] = $request->boolean('is_active');

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan actualizado.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan eliminado.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', Rule::unique('subscription_plans')->ignore($plan?->id)],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'duration_days'     => ['required', 'integer', 'min:1'],
            'max_championships' => ['required', 'integer', 'min:1'],
            'is_active'         => ['sometimes', 'boolean'],
        ]);
    }
}
