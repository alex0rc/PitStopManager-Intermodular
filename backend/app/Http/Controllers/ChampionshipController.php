<?php

namespace App\Http\Controllers;

use App\Http\Requests\Championship\StoreChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use App\Http\Resources\ChampionshipResource;
use App\Http\Resources\RaceResource;
use App\Models\Championship;
use App\Models\Race;
use App\Services\GeocodingService;
use App\Services\SubscriptionQuotaService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChampionshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Championship::with('category')->withCount('races');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['published', 'in_progress', 'finished']);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('season_year')) {
            $query->where('season_year', $request->season_year);
        }

        $championships = $query->paginate(15);

        return ChampionshipResource::collection($championships);
    }

    // --- Destacado home ---
    public function featured(WeatherService $weather): JsonResponse
    {
        $championship = Championship::with('category')
            ->withCount('races')
            ->whereIn('status', ['published', 'in_progress'])
            ->orderByRaw("FIELD(status, 'in_progress', 'published')")
            ->orderByDesc('updated_at')
            ->first();

        if (! $championship) {
            return response()->json(['data' => null]);
        }

        $standings = DB::table('results')
            ->join('races', 'results.race_id', '=', 'races.id')
            ->join('users', 'results.user_id', '=', 'users.id')
            ->where('races.championship_id', $championship->id)
            ->select(
                'users.id as user_id',
                'users.name as pilot_name',
                DB::raw('SUM(results.points) as total_points')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->limit(4)
            ->get()
            ->map(fn ($row) => [
                'user_id'      => $row->user_id,
                'pilot_name'   => $row->pilot_name,
                'total_points' => (int) $row->total_points,
            ]);

        $nextRace = Race::query()
            ->where('championship_id', $championship->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('scheduled_at', '>=', now())
            ->with('circuit')
            ->orderBy('scheduled_at')
            ->first();

        if (! $nextRace) {
            $nextRace = Race::query()
                ->where('championship_id', $championship->id)
                ->with('circuit')
                ->orderByDesc('scheduled_at')
                ->first();
        }

        $weatherData = null;
        $circuit = $nextRace?->circuit;
        $lat = $circuit?->latitude ?? $championship->venue_latitude;
        $lon = $circuit?->longitude ?? $championship->venue_longitude;
        if ($lat !== null && $lon !== null) {
            $weatherData = $weather->getWeather((float) $lat, (float) $lon);
            if (isset($weatherData['error'])) {
                $weatherData = ['error' => $weatherData['error']];
            }
        }

        return response()->json([
            'data' => [
                'championship' => new ChampionshipResource($championship),
                'standings'    => $standings,
                'next_race'    => $nextRace ? new RaceResource($nextRace) : null,
                'weather'      => $weatherData,
            ],
        ]);
    }

    public function myChampionships(Request $request)
    {
        $query = Championship::with('category')->withCount('races')
            ->where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('season_year')) {
            $query->where('season_year', $request->season_year);
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return ChampionshipResource::collection($query->paginate($perPage));
    }

    public function show(Championship $championship): ChampionshipResource
    {
        $championship->load(['category', 'user', 'races.circuit']);
        return new ChampionshipResource($championship);
    }

    public function store(StoreChampionshipRequest $request): JsonResponse
    {
        $this->authorize('create', Championship::class);

        $user = $request->user();

        $quota = app(SubscriptionQuotaService::class);
        if ($user->isOrganizer()) {
            $reason = $quota->createChampionshipDeniedReason($user);
            if ($reason) {
                return response()->json(['message' => $reason], 403);
            }
        }

        $data = $this->applyVenueGeocoding($request->validated());

        $championship = Championship::create(array_merge(
            $data,
            [
                'user_id' => $user->id,
                'status' => 'draft',
            ]
        ));

        return (new ChampionshipResource($championship->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateChampionshipRequest $request, Championship $championship): ChampionshipResource
    {
        $this->authorize('update', $championship);

        $championship->update($this->applyVenueGeocoding($request->validated()));

        return new ChampionshipResource($championship->fresh()->load('category'));
    }

    public function uploadImage(Request $request, Championship $championship): ChampionshipResource
    {
        $this->authorize('update', $championship);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('championships', 'public');
        $championship->update(['image' => $path]);

        return new ChampionshipResource($championship->fresh()->load('category'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyVenueGeocoding(array $data): array
    {
        if (
            empty($data['venue_latitude'])
            && empty($data['venue_longitude'])
            && !empty($data['venue_city'])
        ) {
            $coords = app(GeocodingService::class)->resolve(
                $data['venue_city'],
                $data['venue_province'] ?? null,
                $data['venue_country'] ?? 'España',
            );
            if ($coords) {
                $data['venue_latitude'] = $coords['latitude'];
                $data['venue_longitude'] = $coords['longitude'];
            }
        }

        return $data;
    }

    public function destroy(Championship $championship): JsonResponse
    {
        $this->authorize('delete', $championship);

        $championship->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Championship $championship): ChampionshipResource
    {
        $this->authorize('update', $championship);

        $request->validate([
            'status' => 'required|string|in:draft,published,in_progress,finished,cancelled',
        ]);

        if ($request->status === 'published') {
            $user = $request->user();
            if (!$user->isAdmin()) {
                $quota = app(SubscriptionQuotaService::class);
                if (!$quota->activeSubscription($user)) {
                    abort(403, 'Necesitas una suscripción activa para publicar un campeonato.');
                }
            }
        }

        $previousStatus = $championship->status;
        $championship->update(['status' => $request->status]);
        $fresh = $championship->fresh()->load(['category', 'user']);

        if (
            $request->status === 'published'
            && $previousStatus !== 'published'
            && $fresh->user?->email
        ) {
            \App\Support\MailHelper::sendSafely(
                $fresh->user->email,
                new \App\Mail\ChampionshipPublishedMail($fresh),
                ['championship_id' => $fresh->id, 'type' => 'championship_published'],
            );
        }

        return new ChampionshipResource($fresh);
    }

    public function standings(Championship $championship): JsonResponse
    {
        $standings = DB::table('results')
            ->join('races', 'results.race_id', '=', 'races.id')
            ->join('users', 'results.user_id', '=', 'users.id')
            ->where('races.championship_id', $championship->id)
            ->select(
                'users.id as user_id',
                'users.name as pilot_name',
                DB::raw('SUM(results.points) as total_points')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->get()
            ->map(fn ($row) => [
                'user_id'      => $row->user_id,
                'pilot_name'   => $row->pilot_name,
                'total_points' => (int) $row->total_points,
            ]);

        return response()->json(['data' => $standings]);
    }

    // --- Clasificación por carrera ---
    public function raceResults(Championship $championship): JsonResponse
    {
        $races = $championship->races()
            ->with([
                'circuit',
                'results' => fn ($q) => $q->with('user')->orderBy('position'),
            ])
            ->whereHas('results')
            ->orderBy('scheduled_at')
            ->get();

        $data = $races->map(fn ($race) => [
            'race_id'       => $race->id,
            'race_name'     => $race->name,
            'scheduled_at'  => $race->scheduled_at,
            'status'        => $race->status,
            'circuit_name'  => $race->circuit?->name,
            'results'       => $race->results->map(fn ($result) => [
                'user_id'       => $result->user_id,
                'pilot_name'    => $result->user?->name,
                'position'      => $result->position,
                'best_lap_time' => $result->best_lap_time,
                'total_time'    => $result->total_time,
                'points'        => (int) $result->points,
                'dnf'           => (bool) $result->dnf,
                'dsq'           => (bool) $result->dsq,
            ])->values(),
        ])->values();

        return response()->json(['data' => $data]);
    }
}
