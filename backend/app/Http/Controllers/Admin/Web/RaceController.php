<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Models\Circuit;
use App\Models\Race;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function index(Championship $championship)
    {
        $championship->loadCount('inscriptions');
        $races = $championship->races()
            ->with('circuit')
            ->withCount(['inscriptions', 'results'])
            ->orderBy('scheduled_at')
            ->get();

        $stats = [
            'total'       => $races->count(),
            'scheduled'   => $races->where('status', 'scheduled')->count(),
            'in_progress' => $races->where('status', 'in_progress')->count(),
            'completed'   => $races->where('status', 'completed')->count(),
            'pilots'      => $championship->inscriptions()->where('status', 'confirmed')->count(),
        ];

        return view('admin.races.index', compact('championship', 'races', 'stats'));
    }

    public function create(Championship $championship)
    {
        return view('admin.races.form', [
            'championship' => $championship,
            'race'         => new Race(['status' => 'scheduled']),
            'circuits'     => Circuit::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Championship $championship)
    {
        $data = $this->validated($request);
        $championship->races()->create($data);

        return redirect()->route('admin.championships.races.index', $championship)
            ->with('success', 'Carrera creada correctamente.');
    }

    public function edit(Championship $championship, Race $race)
    {
        abort_unless($race->championship_id === $championship->id, 404);

        $race->loadCount(['inscriptions', 'results']);

        return view('admin.races.form', [
            'championship' => $championship,
            'race'         => $race,
            'circuits'     => Circuit::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Championship $championship, Race $race)
    {
        abort_unless($race->championship_id === $championship->id, 404);

        $race->update($this->validated($request));

        return redirect()->route('admin.championships.races.index', $championship)
            ->with('success', 'Carrera actualizada correctamente.');
    }

    public function destroy(Championship $championship, Race $race)
    {
        abort_unless($race->championship_id === $championship->id, 404);

        if ($race->results()->exists()) {
            return back()->with('error', 'No se puede eliminar: la carrera tiene resultados registrados.');
        }

        $race->delete();

        return redirect()->route('admin.championships.races.index', $championship)
            ->with('success', 'Carrera eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'circuit_id'   => ['required', 'exists:circuits,id'],
            'name'         => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'total_laps'   => ['nullable', 'integer', 'min:1'],
            'status'       => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
