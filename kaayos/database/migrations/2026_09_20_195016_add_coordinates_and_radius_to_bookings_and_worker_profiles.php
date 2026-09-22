<?php

use App\Models\Booking;
use App\Support\TuyBarangays;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('barangay');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('travel_distance_from_prev_km', 6, 2)->nullable()->after('longitude');
            $table->integer('estimated_transit_minutes')->nullable()->after('travel_distance_from_prev_km');
        });

        // Backfill existing bookings with barangay coordinates if available
        try {
            $bookings = Booking::with('client')->get();
            foreach ($bookings as $b) {
                $lat = null;
                $lng = null;
                if ($b->barangay && TuyBarangays::isValidBarangay($b->barangay)) {
                    [$lat, $lng] = TuyBarangays::pointForStatic($b->barangay);
                } elseif ($b->client && $b->client->latitude && $b->client->longitude) {
                    $lat = $b->client->latitude;
                    $lng = $b->client->longitude;
                } else {
                    [$lat, $lng] = TuyBarangays::pointForStatic('Luna');
                }
                $b->update([
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal if table is empty or during clean install
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'travel_distance_from_prev_km',
                'estimated_transit_minutes',
            ]);
        });
    }
};
