<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('reminder_week_sent_at')->nullable()->after('ends_at');
            $table->timestamp('reminder_day_sent_at')->nullable()->after('reminder_week_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['reminder_week_sent_at', 'reminder_day_sent_at']);
        });
    }
};
