<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->string('venue_country')->nullable()->after('image');
            $table->string('venue_province')->nullable()->after('venue_country');
            $table->string('venue_city')->nullable()->after('venue_province');
            $table->decimal('venue_latitude', 10, 8)->nullable()->after('venue_city');
            $table->decimal('venue_longitude', 11, 8)->nullable()->after('venue_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn([
                'venue_country',
                'venue_province',
                'venue_city',
                'venue_latitude',
                'venue_longitude',
            ]);
        });
    }
};
