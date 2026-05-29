<?php

namespace App\Http\Controllers;

use App\Http\Requests\Circuit\StoreCircuitRequest;
use App\Http\Requests\Circuit\UpdateCircuitRequest;
use App\Http\Resources\CircuitResource;
use App\Models\Circuit;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CircuitController extends Controller
{
    public function index(Request $request)
    {
        $query = Circuit::query()->with('user')->where('status', 'approved');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $circuits = $query->orderBy('province')->orderBy('name')->paginate($perPage);

        return CircuitResource::collection($circuits);
    }

    public function myCircuits(Request $request)
    {
        $query = Circuit::query()->with('user')
            ->where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $circuits = $query->orderBy('province')->orderBy('name')->paginate($perPage);

        return CircuitResource::collection($circuits);
    }

    public function provinces(): JsonResponse
    {
        $provinces = Circuit::query()
            ->where('status', 'approved')
            ->whereNotNull('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return response()->json(['data' => $provinces]);
    }

    public function show(Request $request, Circuit $circuit): CircuitResource
    {
        $user = $request->user();

        if ($circuit->status !== 'approved') {
            $canView = $user
                && ($user->isAdmin() || $user->id === $circuit->user_id);
            if (!$canView) {
                abort(404, 'Circuito no encontrado.');
            }
        }

        $circuit->load('user');

        return new CircuitResource($circuit);
    }

    public function store(StoreCircuitRequest $request): JsonResponse
    {
        $this->authorize('create', Circuit::class);

        $user = $request->user();
        $status = $user->isAdmin() ? 'approved' : 'pending';

        $circuit = Circuit::create(array_merge(
            $this->applyGeocoding($request->validated()),
            [
                'user_id' => $user->id,
                'status'  => $status,
            ]
        ));

        $message = $status === 'pending'
            ? 'Circuito enviado. Un administrador debe aprobarlo antes de usarlo en carreras.'
            : 'Circuito creado.';

        return (new CircuitResource($circuit))
            ->additional(['message' => $message])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCircuitRequest $request, Circuit $circuit): CircuitResource
    {
        $this->authorize('update', $circuit);

        $data = $this->applyGeocoding($request->validated());

        // --- Estado circuito ---
        if ($request->user()->isOrganizer() && !$request->user()->isAdmin()) {
            if ($circuit->status === 'rejected') {
                $data['status'] = 'pending';
            } else {
                unset($data['status']);
            }
        }

        $circuit->update($data);

        return new CircuitResource($circuit->fresh());
    }

    public function destroy(Circuit $circuit): JsonResponse
    {
        $this->authorize('delete', $circuit);

        $circuit->delete();

        return response()->json(null, 204);
    }

    public function uploadImage(Request $request, Circuit $circuit): CircuitResource
    {
        $this->authorize('update', $circuit);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('image')->store('circuits', 'public');
        $circuit->update(['image' => $path]);

        return new CircuitResource($circuit->fresh());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyGeocoding(array $data): array
    {
        if (
            (empty($data['latitude']) || empty($data['longitude']))
            && !empty($data['city'])
        ) {
            $coords = app(GeocodingService::class)->resolve(
                $data['city'],
                $data['province'] ?? null,
                $data['country'] ?? 'España',
            );
            if ($coords) {
                $data['latitude'] = $coords['latitude'];
                $data['longitude'] = $coords['longitude'];
            }
        }

        return $data;
    }
}
