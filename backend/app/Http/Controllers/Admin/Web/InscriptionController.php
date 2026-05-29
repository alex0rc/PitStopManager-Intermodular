<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Mail\InscriptionStatusMail;
use App\Models\Championship;
use App\Models\Inscription;
use App\Services\InscriptionRaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscriptionController extends Controller
{
    public function index(Request $request, Championship $championship)
    {
        $championship->loadCount('inscriptions');

        $query = $championship->inscriptions()->with(['user', 'races'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $inscriptions = $query->get();

        $stats = [
            'total'     => $championship->inscriptions()->count(),
            'pending'   => $championship->inscriptions()->where('status', 'pending')->count(),
            'confirmed' => $championship->inscriptions()->where('status', 'confirmed')->count(),
            'rejected'  => $championship->inscriptions()->where('status', 'rejected')->count(),
            'withdrawn' => $championship->inscriptions()->where('status', 'withdrawn')->count(),
        ];

        $races = $championship->races()->orderBy('scheduled_at')->get(['id', 'name', 'status']);

        return view('admin.inscriptions.index', compact(
            'championship',
            'inscriptions',
            'stats',
            'races',
        ));
    }

    public function edit(Championship $championship, Inscription $inscription)
    {
        abort_unless($inscription->championship_id === $championship->id, 404);

        $inscription->load(['user', 'races']);
        $championship->load(['races' => fn ($q) => $q->orderBy('scheduled_at')]);

        return view('admin.inscriptions.form', compact('championship', 'inscription'));
    }

    public function update(Request $request, Championship $championship, Inscription $inscription, InscriptionRaceService $raceService)
    {
        abort_unless($inscription->championship_id === $championship->id, 404);

        $data = $request->validate([
            'car_number' => ['nullable', 'integer', 'min:1'],
            'kart_info'  => ['nullable', 'string', 'max:500'],
            'race_ids'   => ['nullable', 'array'],
            'race_ids.*' => ['integer'],
            'status'     => ['sometimes', 'in:pending,confirmed,rejected,withdrawn'],
        ]);

        $previousStatus = $inscription->status;

        if (array_key_exists('car_number', $data)) {
            $inscription->car_number = $data['car_number'];
        }

        if (array_key_exists('kart_info', $data)) {
            $inscription->kart_info = $championship->usesOwnKarts()
                ? ($data['kart_info'] ?? null)
                : null;
        }

        if (isset($data['status'])) {
            $inscription->status = $data['status'];
        }

        $inscription->save();

        if ($request->has('race_ids')) {
            $raceIds = array_values(array_unique(array_map('intval', $request->input('race_ids', []))));
            $valid = $championship->races()->whereIn('id', $raceIds)->pluck('id')->all();
            $inscription->races()->sync($valid);
        }

        $fresh = $inscription->fresh()->load(['user', 'championship', 'races']);

        if (
            isset($data['status'])
            && in_array($data['status'], ['confirmed', 'rejected'], true)
            && $data['status'] !== $previousStatus
            && $fresh->user
        ) {
            try {
                Mail::to($fresh->user->email)->send(new InscriptionStatusMail($fresh));
            } catch (\Throwable $e) {
                Log::warning('Inscription email failed', ['id' => $fresh->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.championships.inscriptions.index', $championship)
            ->with('success', 'Inscripción actualizada.');
    }

    public function updateStatus(Request $request, Championship $championship, Inscription $inscription)
    {
        abort_unless($inscription->championship_id === $championship->id, 404);

        $request->validate([
            'status' => 'required|in:pending,confirmed,rejected,withdrawn',
        ]);

        $previous = $inscription->status;
        $inscription->update(['status' => $request->status]);
        $fresh = $inscription->fresh()->load(['user', 'championship', 'races']);

        if (in_array($request->status, ['confirmed', 'rejected'], true) && $request->status !== $previous) {
            try {
                Mail::to($fresh->user->email)->send(new InscriptionStatusMail($fresh));
            } catch (\Throwable $e) {
                Log::warning('Inscription email failed', ['id' => $fresh->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Estado actualizado.');
    }

    public function approveAllPending(Championship $championship)
    {
        $pending = $championship->inscriptions()->where('status', 'pending')->with('user')->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'No hay inscripciones pendientes.');
        }

        foreach ($pending as $inscription) {
            $inscription->update(['status' => 'confirmed']);
            $fresh = $inscription->fresh()->load(['user', 'championship', 'races']);
            if ($fresh->user?->email) {
                try {
                    Mail::to($fresh->user->email)->send(new InscriptionStatusMail($fresh));
                } catch (\Throwable $e) {
                    Log::warning('Bulk inscription email failed', ['id' => $fresh->id]);
                }
            }
        }

        return back()->with('success', "Se confirmaron {$pending->count()} inscripción(es) pendientes.");
    }

    public function destroy(Championship $championship, Inscription $inscription)
    {
        abort_unless($inscription->championship_id === $championship->id, 404);
        $inscription->delete();

        return back()->with('success', 'Inscripción eliminada.');
    }
}
