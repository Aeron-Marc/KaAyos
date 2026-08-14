<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('house_no', 255)->nullable()->after('address');
            $table->string('barangay', 255)->nullable()->after('house_no');
        });

        DB::statement("UPDATE bookings SET house_no = SUBSTRING_INDEX(address, ',', 1), barangay = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ',', 2), ',', -1)) WHERE house_no IS NULL");
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['house_no', 'barangay']);
        });
    }
};
