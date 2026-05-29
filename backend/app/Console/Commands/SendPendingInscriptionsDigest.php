<?php

namespace App\Console\Commands;

use App\Services\PendingInscriptionsDigestService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendPendingInscriptionsDigest extends Command
{
    protected $signature = 'inscriptions:send-pending-digest {--date= : Reference date (Y-m-d) for testing}';

    protected $description = 'Email organizers a daily summary of pending championship inscriptions';

    public function handle(PendingInscriptionsDigestService $digest): int
    {
        $reference = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now();

        $result = $digest->sendDailyDigests($reference);

        $this->info("Resúmenes enviados: {$result['sent']}");
        $this->info("Omitidos (sin pendientes, ya enviado o sin email): {$result['skipped']}");

        return self::SUCCESS;
    }
}
