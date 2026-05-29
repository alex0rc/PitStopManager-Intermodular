<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('circuits', function (Blueprint $table) {
            $table->string('province')->nullable()->after('city');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('description');
        });

        DB::table('circuits')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('circuits', function (Blueprint $table) {
            $table->dropColumn(['province', 'status']);
        });
    }
};
