<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\Inscription;
use App\Models\Race;
use App\Models\User;
use Illuminate\Database\Seeder;

class InscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) now()->format('Y');

        $liga    = Championship::where('name', 'Liga Levante Karting '.$year)->firstOrFail();
        $costa   = Championship::where('name', 'Copa Costa Blanca '.$year)->firstOrFail();
        $valencia = Championship::where('name', 'Trofeo Comunitat Valenciana '.$year)->firstOrFail();
        $murcia  = Championship::where('name', 'Campeonato Murcia Amateur '.$year)->firstOrFail();
        $cadetes = Championship::where('name', 'Copa Cadetes Levante '.$year)->firstOrFail();

        $pilots = User::where('role', 'pilot')->orderBy('id')->get();

        if ($pilots->count() < 8) {
            return;
        }

        $ligaRaces    = Race::where('championship_id', $liga->id)->orderBy('scheduled_at')->pluck('id')->all();
        $costaRaces   = Race::where('championship_id', $costa->id)->orderBy('scheduled_at')->pluck('id')->all();
        $valenciaRaces = Race::where('championship_id', $valencia->id)->orderBy('scheduled_at')->pluck('id')->all();
        $murciaRaces  = Race::where('championship_id', $murcia->id)->orderBy('scheduled_at')->pluck('id')->all();
        $cadetesRaces = Race::where('championship_id', $cadetes->id)->orderBy('scheduled_at')->pluck('id')->all();

        // Liga Levante: 10 pilotos confirmados
        foreach ($pilots as $index => $pilot) {
            $inscription = Inscription::updateOrCreate(
                ['user_id' => $pilot->id, 'championship_id' => $liga->id],
                ['status' => 'confirmed', 'car_number' => $index + 1]
            );
            $raceIds = $index >= 7 && count($ligaRaces) > 2
                ? array_slice($ligaRaces, 0, count($ligaRaces) - 1)
                : $ligaRaces;
            $inscription->races()->sync($raceIds);
        }

        // Copa Costa Blanca: 8 pilotos
        foreach ($pilots->take(8) as $index => $pilot) {
            $inscription = Inscription::updateOrCreate(
                ['user_id' => $pilot->id, 'championship_id' => $costa->id],
                ['status' => $index < 6 ? 'confirmed' : 'pending', 'car_number' => 20 + $index]
            );
            if ($inscription->status === 'confirmed') {
                $inscription->races()->sync($costaRaces);
            }
        }

        // Trofeo Valencia: 7 confirmados
        foreach ($pilots->take(7) as $index => $pilot) {
            $inscription = Inscription::updateOrCreate(
                ['user_id' => $pilot->id, 'championship_id' => $valencia->id],
                ['status' => 'confirmed', 'car_number' => 40 + $index]
            );
            $inscription->races()->sync($valenciaRaces);
        }

        // Murcia: 5 pilotos
        foreach ($pilots->slice(2, 5) as $index => $pilot) {
            $inscription = Inscription::updateOrCreate(
                ['user_id' => $pilot->id, 'championship_id' => $murcia->id],
                ['status' => 'confirmed', 'car_number' => 60 + $index]
            );
            $inscription->races()->sync($murciaRaces);
        }

        // Cadetes (kart propio): 4 pilotos jóvenes
        $kartSamples = [
            'Tony Kart Racer 401 / IAME X30',
            'CRG Road Rebel / IAME X30',
            'Birel ART / IAME X30',
            'Formula K / IAME X30',
        ];
        foreach ($pilots->slice(6, 4) as $index => $pilot) {
            $inscription = Inscription::updateOrCreate(
                ['user_id' => $pilot->id, 'championship_id' => $cadetes->id],
                [
                    'status'     => 'confirmed',
                    'car_number' => 80 + $index,
                    'kart_info'  => $kartSamples[$index] ?? null,
                ]
            );
            $inscription->races()->sync($cadetesRaces);
        }

    }
}
