<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionPlanRequest;
use App\Http\Requests\Admin\UpdateSubscriptionPlanRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();
        return SubscriptionPlanResource::collection($plans);
    }

    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::create($request->validated());
        return (new SubscriptionPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SubscriptionPlan $subscriptionPlan): SubscriptionPlanResource
    {
        return new SubscriptionPlanResource($subscriptionPlan);
    }

    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): SubscriptionPlanResource
    {
        $subscriptionPlan->update($request->validated());
        return new SubscriptionPlanResource($subscriptionPlan->fresh());
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        $subscriptionPlan->delete();
        return response()->json(null, 204);
    }
}
