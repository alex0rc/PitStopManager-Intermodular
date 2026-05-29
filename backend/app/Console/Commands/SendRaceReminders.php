<?php

namespace App\Console\Commands;

use App\Services\RaceReminderService;
use Illuminate\Console\Command;

class SendRaceReminders extends Command
{
    protected $signature = 'races:send-reminders {--date= : Reference date (Y-m-d) for testing; reminders target the next calendar day}';

    protected $description = 'Email confirmed pilots a reminder one day before each scheduled race';

    public function handle(RaceReminderService $reminders): int
    {
        $reference = $this->option('date')
            ? \Illuminate\Support\Carbon::parse($this->option('date'))->startOfDay()
            : now();

        $result = $reminders->sendDueReminders($reference);

        $this->info("Carreras mañana (desde {$reference->toDateString()}): {$result['races']}");
        $this->info("Recordatorios enviados: {$result['sent']}");
        $this->info("Omitidos (ya enviados o error): {$result['skipped']}");

        return self::SUCCESS;
    }
}
