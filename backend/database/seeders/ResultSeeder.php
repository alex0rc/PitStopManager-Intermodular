<?php

namespace Database\Seeders;

use App\Models\Race;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        $pointsMap = [1 => 25, 2 => 18, 3 => 15, 4 => 12, 5 => 10, 6 => 8, 7 => 6, 8 => 4, 9 => 2, 10 => 1];

        $pilots = User::where('role', 'pilot')->orderBy('id')->get();
        if ($pilots->count() < 8) {
            return;
        }

        $raceConfigs = [
            ['name' => 'Ronda 1 — Karting Alacant', 'pilots' => 8, 'base_lap' => 42.0],
            ['name' => 'Ronda 2 — Lucas Guerrero', 'pilots' => 8, 'base_lap' => 65.0],
            ['name' => 'GP Alicante', 'pilots' => 6, 'base_lap' => 41.0],
            ['name' => 'Ronda indoor Burjassot', 'pilots' => 6, 'base_lap' => 38.5],
            ['name' => 'GP Mar Menor', 'pilots' => 5, 'base_lap' => 62.0],
            ['name' => 'Cadetes — Chiva', 'pilots' => 4, 'base_lap' => 66.0],
            ['name' => 'Etapa Levante — Chiva', 'pilots' => 6, 'base_lap' => 64.0],
        ];

        foreach ($raceConfigs as $config) {
            $race = Race::where('name', $config['name'])->first();
            if (! $race) {
                continue;
            }

            foreach ($pilots->take($config['pilots']) as $index => $pilot) {
                $position = $index + 1;
                $lapSec = $config['base_lap'] + ($index * 0.45);
                $bestLap = sprintf('00:%02d.%03d', (int) floor($lapSec), (int) round(($lapSec - floor($lapSec)) * 1000));
                $totalSec = $lapSec * 18 + $index;
                $totalTime = sprintf('%02d:%02d.%03d', (int) floor($totalSec / 60), (int) floor($totalSec % 60), (int) round(($totalSec - floor($totalSec)) * 1000));

                Result::updateOrCreate(
                    ['race_id' => $race->id, 'user_id' => $pilot->id],
                    [
                        'position'      => $position,
                        'best_lap_time' => $bestLap,
                        'total_time'    => $totalTime,
                        'points'        => $pointsMap[$position] ?? 0,
                        'dnf'           => false,
                        'dsq'           => false,
                    ]
                );
            }
        }
    }
}
