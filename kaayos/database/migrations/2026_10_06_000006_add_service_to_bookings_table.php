<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('service_category')
                ->constrained('services')->nullOnDelete();
            $table->string('service_name')->nullable()->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('service_name');
        });
    }
};
