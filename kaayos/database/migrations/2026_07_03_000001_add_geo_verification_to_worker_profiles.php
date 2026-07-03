<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->boolean('government_id_verified')->default(false)->after('cover_photo');
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('government_id_verified');
            $table->decimal('current_latitude', 10, 7)->nullable()->after('average_rating');
            $table->decimal('current_longitude', 10, 7)->nullable()->after('current_latitude');
            $table->unsignedSmallInteger('service_radius_km')->nullable()->after('service_radius');
        });
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'government_id_verified',
                'average_rating',
                'current_latitude',
                'current_longitude',
                'service_radius_km',
            ]);
        });
    }
};
