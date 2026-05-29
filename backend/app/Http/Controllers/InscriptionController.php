<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inscription\StoreInscriptionRequest;
use App\Http\Requests\Inscription\UpdateInscriptionRacesRequest;
use App\Http\Resources\InscriptionResource;
use App\Mail\InscriptionStatusMail;
use App\Mail\InscriptionSubmittedMail;
use App\Mail\NewInscriptionOrganizerMail;
use App\Support\MailHelper;
use App\Models\Championship;
use App\Models\Inscription;
use App\Models\Race;
use App\Services\InscriptionRaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscriptionController extends Controller
{
    public function index(Request $request, Championship $championship)
    {
        $user = $request->user();
        $isOwner = $user->isAdmin() || ($user->isOrganizer() && $user->id === $championship->user_id);

        $query = $championship->inscriptions()->with(['user', 'races']);

        // --- Visibilidad listado ---
        if (!$isOwner) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'confirmed')
                  ->orWhere('user_id', $user->id);
            });
        }

        return InscriptionResource::collection($query->get());
    }

    public function store(StoreInscriptionRequest $request, Championship $championship): JsonResponse
    {
        $this->authorize('create', [Inscription::class, $championship]);

        if ($championship->status !== 'published') {
            return response()->json(['message' => 'Este campeonato no está abierto a inscripciones.'], 403);
        }

        $user = $request->user();

        if ($user->isOrganizer() && (int) $championship->user_id === (int) $user->id) {
            return response()->json(['message' => 'No puedes inscribirte en un campeonato que organizas.'], 422);
        }

        if (!$user->pilotProfile && ($user->isPilot() || $user->isOrganizer())) {
            $user->pilotProfile()->create([]);
            $user->load('pilotProfile');
        }

        if ($championship->inscriptions()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Ya estás inscrito en este campeonato.'], 422);
        }

        $inscription = $championship->inscriptions()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'car_number' => $request->validated('car_number'),
            'kart_info' => $championship->usesOwnKarts() ? $request->validated('kart_info') : null,
        ]);

        app(InscriptionRaceService::class)->syncForChampionship(
            $inscription,
            $championship,
            $request->input('race_ids'),
        );

        $fresh = $inscription->load(['user', 'races', 'championship.user']);

        if ($fresh->user?->email) {
            MailHelper::sendSafely(
                $fresh->user->email,
                new InscriptionSubmittedMail($fresh),
                ['inscription_id' => $fresh->id, 'type' => 'inscription_submitted'],
            );
        }

        $organizer = $fresh->championship?->user;
        if ($organizer?->email && (int) $organizer->id !== (int) $user->id) {
            MailHelper::sendSafely(
                $organizer->email,
                new NewInscriptionOrganizerMail($fresh),
                ['inscription_id' => $fresh->id, 'type' => 'new_inscription_organizer'],
            );
        }

        return (new InscriptionResource($fresh))
            ->response()
            ->setStatusCode(201);
    }

    public function updateRaces(
        UpdateInscriptionRacesRequest $request,
        Inscription $inscription,
        InscriptionRaceService $raceService,
    ): InscriptionResource {
        $user = $request->user();
        $championship = $inscription->championship;

        if (($user->isPilot() || $user->isOrganizer()) && $user->id === $inscription->user_id) {
            $this->authorize('updateRaces', $inscription);
            $raceService->syncForChampionship($inscription, $championship, $request->input('race_ids'));
        } else {
            $this->authorize('manageRaces', $inscription);
            $raceService->syncForOrganizer($inscription, $championship, $request->input('race_ids'));

            if ($inscription->races()->count() === 0) {
                $inscription->update(['status' => 'withdrawn']);
            }
        }

        $updates = [];
        if ($request->has('car_number')) {
            $updates['car_number'] = $request->validated('car_number');
        }
        if ($request->has('kart_info')) {
            $updates['kart_info'] = $championship->usesOwnKarts()
                ? $request->validated('kart_info')
                : null;
        }
        if ($updates !== []) {
            $inscription->update($updates);
        }

        return new InscriptionResource(
            $inscription->fresh()->load(['user', 'championship', 'races']),
        );
    }

    public function updateStatus(Request $request, Inscription $inscription): InscriptionResource
    {
        $inscription->loadMissing('championship');
        $this->authorize('updateStatus', $inscription);

        $request->validate([
            'status' => 'required|string|in:pending,confirmed,rejected,withdrawn',
        ]);

        $previousStatus = $inscription->status;
        $inscription->update(['status' => $request->status]);
        $fresh = $inscription->fresh()->load(['user', 'championship', 'races']);

        // --- Email estado ---
        if (
            in_array($request->status, ['confirmed', 'rejected'], true)
            && $request->status !== $previousStatus
            && $fresh->user
        ) {
            try {
                Mail::to($fresh->user->email)->send(new InscriptionStatusMail($fresh));
            } catch (\Throwable $e) {
                Log::warning('Failed to send inscription status email', [
                    'inscription_id' => $fresh->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return new InscriptionResource($fresh);
    }

    public function detachRace(
        Inscription $inscription,
        Race $race,
        InscriptionRaceService $raceService,
    ): InscriptionResource {
        $this->authorize('manageRaces', $inscription);

        abort_unless($race->championship_id === $inscription->championship_id, 404);

        $remaining = $inscription->races()
            ->where('races.id', '!=', $race->id)
            ->pluck('races.id')
            ->all();

        $raceService->syncForOrganizer($inscription, $inscription->championship, $remaining);

        if ($inscription->races()->count() === 0) {
            $inscription->update(['status' => 'withdrawn']);
        }

        return new InscriptionResource(
            $inscription->fresh()->load(['user', 'races', 'championship'])
        );
    }

    public function destroy(Request $request, Inscription $inscription)
    {
        $this->authorize('delete', $inscription);

        $user = $request->user();

        // --- Baja ---
        if ($user->isPilot() && $user->id === $inscription->user_id) {
            $inscription->update(['status' => 'withdrawn']);
            $inscription->races()->detach();

            return new InscriptionResource($inscription->fresh()->load(['user', 'races', 'championship']));
        }

        $inscription->delete();

        return response()->json(null, 204);
    }

    public function myInscriptions(Request $request)
    {
        $inscriptions = Inscription::where('user_id', $request->user()->id)
            ->with(['championship', 'races'])
            ->get();

        return InscriptionResource::collection($inscriptions);
    }
}
