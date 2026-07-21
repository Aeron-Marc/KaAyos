<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('agreed_by_client_at')->nullable()->after('barangay');
            $table->timestamp('agreed_by_worker_at')->nullable()->after('agreed_by_client_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['agreed_by_client_at', 'agreed_by_worker_at']);
        });
    }
};
