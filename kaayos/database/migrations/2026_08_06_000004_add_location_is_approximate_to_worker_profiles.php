<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->boolean('location_is_approximate')->default(true)->after('current_longitude');
        });

        DB::table('worker_profiles')
            ->whereNotNull('user_id')
            ->update(['location_is_approximate' => true]);
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn('location_is_approximate');
        });
    }
};
