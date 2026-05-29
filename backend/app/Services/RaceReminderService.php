<?php

namespace App\Services;

use App\Mail\RaceReminderMail;
use App\Models\Inscription;
use App\Models\Race;
use App\Models\RaceReminderSend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RaceReminderService
{
    // --- Recordatorios ---
    /**
     * @return array{sent: int, skipped: int, races: int}
     */
    public function sendDueReminders(?Carbon $referenceDay = null): array
    {
        $referenceDay ??= now();
        $tomorrowStart = $referenceDay->copy()->addDay()->startOfDay();
        $tomorrowEnd = $referenceDay->copy()->addDay()->endOfDay();

        $races = Race::query()
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$tomorrowStart, $tomorrowEnd])
            ->with(['championship', 'circuit'])
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($races as $race) {
            $inscriptions = Inscription::query()
                ->where('championship_id', $race->championship_id)
                ->where('status', 'confirmed')
                ->whereHas('races', fn ($q) => $q->where('races.id', $race->id))
                ->with('user')
                ->get();

            foreach ($inscriptions as $inscription) {
                $user = $inscription->user;
                if (!$user?->email) {
                    $skipped++;
                    continue;
                }

                $alreadySent = RaceReminderSend::query()
                    ->where('race_id', $race->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadySent) {
                    $skipped++;
                    continue;
                }

                try {
                    Mail::to($user->email)->send(new RaceReminderMail($user, $race, $inscription));

                    RaceReminderSend::create([
                        'race_id' => $race->id,
                        'user_id' => $user->id,
                        'sent_at' => now(),
                    ]);

                    $sent++;

                    if (app()->runningInConsole()) {
                        sleep(2);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send race reminder email', [
                        'race_id' => $race->id,
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                    $skipped++;
                }
            }
        }

        return [
            'sent'   => $sent,
            'skipped' => $skipped,
            'races'  => $races->count(),
        ];
    }
}
