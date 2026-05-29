<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePilotProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\PilotProfileResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user()->load('pilotProfile');
        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());
        return new UserResource($user->fresh());
    }

    public function uploadAvatar(Request $request): UserResource
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return new UserResource($user->fresh());
    }

    public function showPilotProfile(Request $request): PilotProfileResource
    {
        $profile = $request->user()->pilotProfile;

        if (!$profile) {
            abort(404, 'Perfil de piloto no encontrado.');
        }

        return new PilotProfileResource($profile);
    }

    public function updatePilotProfile(UpdatePilotProfileRequest $request): PilotProfileResource
    {
        $user = $request->user();
        $profile = $user->pilotProfile;

        if (!$profile) {
            abort(404, 'Perfil de piloto no encontrado.');
        }

        $profile->update($request->validated());

        return new PilotProfileResource($profile->fresh());
    }
}
