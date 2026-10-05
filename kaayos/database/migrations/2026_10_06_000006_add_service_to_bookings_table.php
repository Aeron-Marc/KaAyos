<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // service_id may already exist when an earlier-ordered migration
            // (2026_10_06_000002_add_pricing_and_extras_tables) added it first.
            if (!Schema::hasColumn('bookings', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('service_category')
                    ->constrained('services')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'service_name')) {
                $table->string('service_name')->nullable()->after('service_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'service_name')) {
                $table->dropColumn('service_name');
            }
            if (Schema::hasColumn('bookings', 'service_id')) {
                $table->dropConstrainedForeignId('service_id');
            }
        });
    }
};
