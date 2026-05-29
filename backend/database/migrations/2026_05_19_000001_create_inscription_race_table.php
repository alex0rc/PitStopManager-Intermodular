<?php

use App\Models\Inscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscription_race', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->unique(['inscription_id', 'race_id']);
        });

        // --- Datos iniciales ---
        Inscription::query()
            ->with('championship.races')
            ->chunkById(100, function ($inscriptions) {
                foreach ($inscriptions as $inscription) {
                    $raceIds = $inscription->championship?->races->pluck('id')->all() ?? [];
                    if ($raceIds !== []) {
                        $inscription->races()->syncWithoutDetaching($raceIds);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_race');
    }
};
