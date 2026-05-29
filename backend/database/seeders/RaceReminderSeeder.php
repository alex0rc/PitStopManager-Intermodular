<?php

namespace Database\Seeders;

use App\Services\RaceReminderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RaceReminderSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(RaceReminderService::class);
        $result = $service->sendDueReminders(today());

        $this->command?->info("Carreras mañana ({$result['races']}): recordatorios enviados {$result['sent']}, omitidos {$result['skipped']}");

        if ($result['races'] === 0) {
            $this->command?->warn(
                'No hay carreras programadas para mañana. Comprueba que RaceSeeder incluye «GP Campillos (mañana)».'
            );
        }

        if ($result['sent'] === 0 && $result['races'] > 0) {
            $this->command?->warn(
                'Ningún email de carrera enviado (¿ya enviados, sin inscripciones confirmadas o límite Mailtrap?). '
                . 'Prueba: php artisan races:send-reminders'
            );
        }

        if ($result['sent'] > 0) {
            Log::info('RaceReminderSeeder completed', $result);
        }
    }
}
