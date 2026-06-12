<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\Circuit;
use App\Models\Race;
use Illuminate\Database\Seeder;

class RaceSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) now()->format('Y');

        $liga      = Championship::where('name', 'Liga Levante Karting '.$year)->firstOrFail();
        $costa     = Championship::where('name', 'Copa Costa Blanca '.$year)->firstOrFail();
        $valencia  = Championship::where('name', 'Trofeo Comunitat Valenciana '.$year)->firstOrFail();
        $murcia    = Championship::where('name', 'Campeonato Murcia Amateur '.$year)->firstOrFail();
        $cadetes   = Championship::where('name', 'Copa Cadetes Levante '.$year)->firstOrFail();
        $iberian   = Championship::where('name', 'Iberian Kart Tour '.$year)->firstOrFail();

        $lucas     = $this->circuit('kartodromo-lucas-guerrero');
        $gilesias  = $this->circuit('racing-center-gilesias');
        $alacant   = $this->circuit('karting-alacant');
        $marMenor  = $this->circuit('gokarts-mar-menor');
        $cheste    = $this->circuit('circuit-ricardo-tormo');
        $horta     = $this->circuit('karting-horta-nord');
        $losGarres = $this->circuit('karting-los-garres');
        $nucia     = $this->circuit('karting-nucia-outdoor');
        $zuera     = $this->circuit('circuito-zuera-zaragoza');
        $dakart    = $this->circuit('dakart-indoor-burjassot');

        $races = [
            // Liga Levante — carreras pasadas (resultados) + próximas (tiempo / recordatorios)
            [
                'championship_id' => $liga->id,
                'circuit_id'      => $alacant->id,
                'name'            => 'Ronda 1 — Karting Alacant',
                'scheduled_at'    => now()->subWeeks(3)->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 18,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $liga->id,
                'circuit_id'      => $lucas->id,
                'name'            => 'Ronda 2 — Lucas Guerrero',
                'scheduled_at'    => now()->subWeeks(1)->setTime(11, 0)->toDateTimeString(),
                'total_laps'      => 16,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $liga->id,
                'circuit_id'      => $lucas->id,
                'name'            => 'Ronda 3 — Lucas Guerrero (esta semana)',
                'scheduled_at'    => now()->addDays(2)->setTime(10, 30)->toDateTimeString(),
                'total_laps'      => 20,
                'status'          => 'scheduled',
            ],
            [
                'championship_id' => $liga->id,
                'circuit_id'      => $gilesias->id,
                'name'            => 'Ronda 4 — Gilesias (mañana)',
                'scheduled_at'    => today()->addDay()->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 22,
                'status'          => 'scheduled',
            ],
            [
                'championship_id' => $liga->id,
                'circuit_id'      => $marMenor->id,
                'name'            => 'Ronda 5 — Mar Menor',
                'scheduled_at'    => now()->addWeeks(2)->setTime(11, 0)->toDateTimeString(),
                'total_laps'      => 20,
                'status'          => 'scheduled',
            ],

            // Copa Costa Blanca
            [
                'championship_id' => $costa->id,
                'circuit_id'      => $alacant->id,
                'name'            => 'GP Alicante',
                'scheduled_at'    => now()->subWeeks(2)->setTime(17, 0)->toDateTimeString(),
                'total_laps'      => 15,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $costa->id,
                'circuit_id'      => $gilesias->id,
                'name'            => 'GP San Fulgencio',
                'scheduled_at'    => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 18,
                'status'          => 'scheduled',
            ],
            [
                'championship_id' => $costa->id,
                'circuit_id'      => $nucia->id,
                'name'            => 'GP Costa Blanca — Benidorm',
                'scheduled_at'    => now()->addWeeks(3)->setTime(18, 0)->toDateTimeString(),
                'total_laps'      => 16,
                'status'          => 'scheduled',
            ],

            // Trofeo Comunitat Valenciana
            [
                'championship_id' => $valencia->id,
                'circuit_id'      => $dakart->id,
                'name'            => 'Ronda indoor Burjassot',
                'scheduled_at'    => now()->subDays(10)->setTime(16, 0)->toDateTimeString(),
                'total_laps'      => 12,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $valencia->id,
                'circuit_id'      => $cheste->id,
                'name'            => 'Ronda Cheste',
                'scheduled_at'    => now()->addDays(7)->setTime(9, 30)->toDateTimeString(),
                'total_laps'      => 14,
                'status'          => 'scheduled',
            ],
            [
                'championship_id' => $valencia->id,
                'circuit_id'      => $horta->id,
                'name'            => 'Ronda Horta Nord',
                'scheduled_at'    => now()->addWeeks(4)->setTime(11, 0)->toDateTimeString(),
                'total_laps'      => 15,
                'status'          => 'scheduled',
            ],

            // Murcia Amateur
            [
                'championship_id' => $murcia->id,
                'circuit_id'      => $marMenor->id,
                'name'            => 'GP Mar Menor',
                'scheduled_at'    => now()->subWeek()->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 20,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $murcia->id,
                'circuit_id'      => $losGarres->id,
                'name'            => 'GP Los Garres',
                'scheduled_at'    => now()->addDays(10)->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 18,
                'status'          => 'scheduled',
            ],

            // Cadetes Levante
            [
                'championship_id' => $cadetes->id,
                'circuit_id'      => $lucas->id,
                'name'            => 'Cadetes — Chiva',
                'scheduled_at'    => now()->subDays(5)->setTime(11, 0)->toDateTimeString(),
                'total_laps'      => 12,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $cadetes->id,
                'circuit_id'      => $cheste->id,
                'name'            => 'Cadetes — Cheste',
                'scheduled_at'    => now()->addWeeks(1)->setTime(11, 0)->toDateTimeString(),
                'total_laps'      => 12,
                'status'          => 'scheduled',
            ],

            // Iberian tour
            [
                'championship_id' => $iberian->id,
                'circuit_id'      => $lucas->id,
                'name'            => 'Etapa Levante — Chiva',
                'scheduled_at'    => now()->subMonths(2)->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 18,
                'status'          => 'completed',
            ],
            [
                'championship_id' => $iberian->id,
                'circuit_id'      => $zuera->id,
                'name'            => 'Etapa Zuera',
                'scheduled_at'    => now()->addMonths(1)->setTime(10, 0)->toDateTimeString(),
                'total_laps'      => 20,
                'status'          => 'scheduled',
            ],
        ];

        foreach ($races as $row) {
            Race::updateOrCreate(
                ['championship_id' => $row['championship_id'], 'name' => $row['name']],
                $row
            );
        }
    }

    private function circuit(string $slug): Circuit
    {
        return Circuit::where('slug', $slug)->firstOrFail();
    }
}
