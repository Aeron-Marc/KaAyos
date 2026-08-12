<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tuyBarangays = [
        'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
        'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
        'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
        'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('barangay');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('region')->nullable()->after('longitude');
            $table->string('province')->nullable()->after('region');
            $table->string('city_municipality')->nullable()->after('province');
            $table->string('street_address')->nullable()->after('city_municipality');
            $table->enum('location_source', ['gps', 'manual'])->nullable()->after('street_address');
        });

        // Backfill legacy Tuy data into the new structured columns.
        $rows = DB::table('users')->whereNotNull('barangay')->orWhereNotNull('city')->get(['id', 'barangay', 'city']);
        foreach ($rows as $row) {
            $barangay = in_array($row->barangay, $this->tuyBarangays, true) ? $row->barangay
                : (in_array($row->city, $this->tuyBarangays, true) ? $row->city : null);

            $update = [
                'region'            => 'Calabarzon',
                'province'          => 'Batangas',
                'city_municipality' => 'Tuy',
                'location_source'   => 'manual',
            ];

            if ($barangay !== null) {
                $update['barangay'] = $barangay;
            }

            DB::table('users')->where('id', $row->id)->update($update);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'region', 'province', 'city_municipality', 'street_address', 'location_source']);
        });
    }
};
