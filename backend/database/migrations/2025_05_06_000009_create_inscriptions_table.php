<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'withdrawn'])->default('pending');
            $table->integer('car_number')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'championship_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
