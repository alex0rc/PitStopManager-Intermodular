<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->string('kart_modality', 20)->default('rental')->after('category_id');
            $table->string('engine_class', 120)->nullable()->after('kart_modality');
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->string('kart_info', 500)->nullable()->after('car_number');
        });
    }

    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn(['kart_modality', 'engine_class']);
        });

        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn('kart_info');
        });
    }
};
