<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->nullable();
            $table->string('best_lap_time')->nullable();
            $table->string('total_time')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('dnf')->default(false);
            $table->boolean('dsq')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
