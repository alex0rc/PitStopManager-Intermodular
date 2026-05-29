<?php

namespace App\Http\Controllers;

use App\Http\Requests\Race\StoreRaceRequest;
use App\Http\Requests\Race\UpdateRaceRequest;
use App\Http\Resources\RaceResource;
use App\Models\Championship;
use App\Models\Race;
use Illuminate\Http\JsonResponse;

class RaceController extends Controller
{
    public function index(Championship $championship)
    {
        $races = $championship->races()->with('circuit')->get();
        return RaceResource::collection($races);
    }

    public function show(Race $race): RaceResource
    {
        $race->load(['championship', 'circuit', 'results.user']);
        return new RaceResource($race);
    }

    public function store(StoreRaceRequest $request, Championship $championship): JsonResponse
    {
        $this->authorize('update', $championship);

        $race = $championship->races()->create($request->validated());

        return (new RaceResource($race->load('circuit')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateRaceRequest $request, Race $race): RaceResource
    {
        $this->authorize('update', $race);

        $race->update($request->validated());

        return new RaceResource($race->fresh()->load('circuit'));
    }

    public function destroy(Race $race): JsonResponse
    {
        $this->authorize('delete', $race);

        $race->delete();

        return response()->json(null, 204);
    }
}
