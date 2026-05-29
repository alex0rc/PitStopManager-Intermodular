<?php

namespace App\Http\Controllers;

use App\Http\Requests\Result\StoreResultRequest;
use App\Http\Requests\Result\UpdateResultRequest;
use App\Http\Resources\ResultResource;
use App\Models\Race;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Race $race)
    {
        $results = $race->results()->with('user')->orderBy('position')->get();
        return ResultResource::collection($results);
    }

    public function store(StoreResultRequest $request, Race $race): JsonResponse
    {
        $this->authorize('create', [Result::class, $race]);

        $userId = $request->validated('user_id');

        $isInscribed = \App\Models\Inscription::where('user_id', $userId)
            ->where('championship_id', $race->championship_id)
            ->where('status', 'confirmed')
            ->whereHas('races', fn ($q) => $q->where('races.id', $race->id))
            ->exists();

        if (!$isInscribed) {
            return response()->json(['message' => 'El piloto no está inscrito en esta carrera.'], 422);
        }

        $result = $race->results()->create($request->validated());

        return (new ResultResource($result->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateResultRequest $request, Result $result): ResultResource
    {
        $this->authorize('update', $result);

        $result->update($request->validated());

        return new ResultResource($result->fresh()->load('user'));
    }

    public function destroy(Result $result): JsonResponse
    {
        $this->authorize('delete', $result);

        $result->delete();

        return response()->json(null, 204);
    }

    public function myResults(Request $request)
    {
        $results = Result::where('user_id', $request->user()->id)
            ->with(['race.championship'])
            ->get();

        return ResultResource::collection($results);
    }
}
