<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Inscription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InscriptionRaceService
{
    public const SELECTABLE_STATUSES = ['scheduled', 'in_progress'];

    /**
     * @param  array<int>|null  $raceIds
     * @return array<int>
     */
    public function resolveRaceIds(Championship $championship, ?array $raceIds): array
    {
        $selectable = $this->selectableRaceIds($championship);

        if ($selectable === []) {
            return [];
        }

        $raceIds = array_values(array_unique(array_map('intval', $raceIds ?? [])));

        $validator = Validator::make(
            ['race_ids' => $raceIds],
            [
                'race_ids' => ['required', 'array', 'min:1'],
                'race_ids.*' => ['integer'],
            ],
            [
                'race_ids.required' => 'Selecciona al menos una carrera del campeonato.',
                'race_ids.min' => 'Selecciona al menos una carrera del campeonato.',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $invalid = array_diff($raceIds, $selectable);
        if ($invalid !== []) {
            $validator = Validator::make([], []);
            $validator->errors()->add(
                'race_ids',
                'Una o más carreras no pertenecen al campeonato o no están abiertas para inscripción.'
            );
            throw new ValidationException($validator);
        }

        return $raceIds;
    }

    /**
     * @param  array<int>|null  $raceIds
     */
    public function syncForChampionship(Inscription $inscription, Championship $championship, ?array $raceIds): void
    {
        $inscription->races()->sync($this->resolveRaceIds($championship, $raceIds));
    }

    // --- Organizador ---
    /**
     * @param  array<int>|null  $raceIds
     * @return array<int>
     */
    public function resolveRaceIdsForOrganizer(Championship $championship, ?array $raceIds): array
    {
        $validIds = $championship->races()->pluck('id')->all();

        if ($validIds === []) {
            return [];
        }

        $raceIds = array_values(array_unique(array_map('intval', $raceIds ?? [])));

        $invalid = array_diff($raceIds, $validIds);
        if ($invalid !== []) {
            $validator = Validator::make([], []);
            $validator->errors()->add(
                'race_ids',
                'Una o más carreras no pertenecen a este campeonato.'
            );
            throw new ValidationException($validator);
        }

        return $raceIds;
    }

    /**
     * @param  array<int>|null  $raceIds
     */
    public function syncForOrganizer(Inscription $inscription, Championship $championship, ?array $raceIds): void
    {
        $inscription->races()->sync(
            $this->resolveRaceIdsForOrganizer($championship, $raceIds)
        );
    }

    /** @return array<int> */
    public function selectableRaceIds(Championship $championship): array
    {
        return $championship->races()
            ->whereIn('status', self::SELECTABLE_STATUSES)
            ->orderBy('scheduled_at')
            ->pluck('id')
            ->all();
    }
}
