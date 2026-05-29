<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Models\Race;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Race $race)
    {
        $race->load(['championship', 'circuit', 'results.user']);

        return view('admin.results.index', compact('race'));
    }

    public function create(Race $race)
    {
        $pilots = User::where('role', 'pilot')
            ->whereIn('id', Inscription::where('championship_id', $race->championship_id)
                ->where('status', 'confirmed')
                ->whereHas('races', fn ($q) => $q->where('races.id', $race->id))
                ->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('admin.results.form', [
            'race'   => $race,
            'result' => new Result(),
            'pilots' => $pilots,
        ]);
    }

    public function store(Request $request, Race $race)
    {
        $data = $this->validated($request);

        $isInscribed = Inscription::where('user_id', $data['user_id'])
            ->where('championship_id', $race->championship_id)
            ->where('status', 'confirmed')
            ->whereHas('races', fn ($q) => $q->where('races.id', $race->id))
            ->exists();

        if (!$isInscribed) {
            return back()->withInput()->with('error', 'El piloto no está inscrito en esta carrera.');
        }

        $race->results()->create($data);

        return redirect()->route('admin.races.results.index', $race)
            ->with('success', 'Resultado registrado.');
    }

    public function edit(Race $race, Result $result)
    {
        abort_unless($result->race_id === $race->id, 404);

        $pilots = User::where('role', 'pilot')->orderBy('name')->get();

        return view('admin.results.form', compact('race', 'result', 'pilots'));
    }

    public function update(Request $request, Race $race, Result $result)
    {
        abort_unless($result->race_id === $race->id, 404);

        $result->update($this->validated($request));

        return redirect()->route('admin.races.results.index', $race)
            ->with('success', 'Resultado actualizado.');
    }

    public function destroy(Race $race, Result $result)
    {
        abort_unless($result->race_id === $race->id, 404);
        $result->delete();

        return redirect()->route('admin.races.results.index', $race)
            ->with('success', 'Resultado eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'user_id'       => ['required', 'exists:users,id'],
            'position'      => ['nullable', 'integer', 'min:1'],
            'best_lap_time' => ['nullable', 'string', 'max:20'],
            'total_time'    => ['nullable', 'string', 'max:20'],
            'points'        => ['nullable', 'integer', 'min:0'],
            'dnf'           => ['sometimes', 'boolean'],
            'dsq'           => ['sometimes', 'boolean'],
            'notes'         => ['nullable', 'string'],
        ]);

        $data['dnf'] = $request->boolean('dnf');
        $data['dsq'] = $request->boolean('dsq');
        $data['points'] = $data['points'] ?? 0;

        return $data;
    }
}
