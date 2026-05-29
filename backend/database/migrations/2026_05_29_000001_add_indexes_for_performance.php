<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->index('status');
            $table->index('season_year');
        });

        Schema::table('races', function (Blueprint $table) {
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['season_year']);
        });

        Schema::table('races', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['scheduled_at']);
        });
    }
};
