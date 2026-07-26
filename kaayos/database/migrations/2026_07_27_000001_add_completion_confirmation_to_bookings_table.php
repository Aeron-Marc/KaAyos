<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Track who initiated the completion request (worker or client)
            $table->foreignId('completion_requested_by')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->after('cancelled_at');

            // When did someone request completion
            $table->datetime('completion_requested_at')
                  ->nullable()
                  ->after('completion_requested_by');

            // Track when each party confirmed completion
            $table->datetime('confirmed_by_worker_at')
                  ->nullable()
                  ->after('completion_requested_at');

            $table->datetime('confirmed_by_client_at')
                  ->nullable()
                  ->after('confirmed_by_worker_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeignIdFor('completion_requested_by');
            $table->dropColumn([
                'completion_requested_by',
                'completion_requested_at',
                'confirmed_by_worker_at',
                'confirmed_by_client_at',
            ]);
        });
    }
};
