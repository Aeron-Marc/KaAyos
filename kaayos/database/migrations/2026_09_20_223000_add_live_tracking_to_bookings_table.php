<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('worker_live_latitude', 10, 7)->nullable()->after('estimated_transit_minutes');
            $table->decimal('worker_live_longitude', 10, 7)->nullable()->after('worker_live_latitude');
            $table->float('worker_live_heading')->nullable()->after('worker_live_longitude');
            $table->float('worker_live_speed')->nullable()->after('worker_live_heading');
            $table->timestamp('worker_live_updated_at')->nullable()->after('worker_live_speed');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'worker_live_latitude',
                'worker_live_longitude',
                'worker_live_heading',
                'worker_live_speed',
                'worker_live_updated_at',
            ]);
        });
    }
};

