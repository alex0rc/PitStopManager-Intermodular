<?php

namespace App\Services;

use App\Mail\PendingInscriptionsDigestMail;
use App\Models\Championship;
use App\Models\User;
use App\Support\MailHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PendingInscriptionsDigestService
{
    /**
     * @return array{sent: int, skipped: int}
     */
    public function sendDailyDigests(?Carbon $referenceDay = null): array
    {
        $referenceDay ??= now();
        $dateKey = $referenceDay->toDateString();

        $rows = Championship::query()
            ->whereHas('inscriptions', fn ($q) => $q->where('status', 'pending'))
            ->with(['user', 'inscriptions' => fn ($q) => $q->where('status', 'pending')])
            ->get();

        $byOrganizer = $rows->groupBy('user_id');
        $sent = 0;
        $skipped = 0;

        foreach ($byOrganizer as $organizerId => $championships) {
            /** @var User|null $organizer */
            $organizer = $championships->first()?->user;
            if (!$organizer?->email || !$organizer->isOrganizer()) {
                $skipped++;
                continue;
            }

            $cacheKey = "pending_inscriptions_digest:{$organizer->id}:{$dateKey}";
            if (Cache::has($cacheKey)) {
                $skipped++;
                continue;
            }

            $summary = $championships->map(fn (Championship $c) => [
                'championship_id'   => $c->id,
                'championship_name' => $c->name,
                'pending_count'     => $c->inscriptions->count(),
            ])->filter(fn (array $row) => $row['pending_count'] > 0)->values();

            $totalPending = (int) $summary->sum('pending_count');
            if ($totalPending === 0) {
                $skipped++;
                continue;
            }

            MailHelper::sendSafely(
                $organizer->email,
                new PendingInscriptionsDigestMail($organizer, $summary, $totalPending),
                ['organizer_id' => $organizer->id, 'type' => 'pending_digest'],
            );

            Cache::put($cacheKey, true, $referenceDay->copy()->endOfDay());
            $sent++;

            if (app()->runningInConsole()) {
                sleep(2);
            }
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
