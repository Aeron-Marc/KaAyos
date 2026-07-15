<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index('scheduled_at');
            $table->index(['client_id', 'status']);
            $table->index(['worker_id', 'status']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['booking_id', 'created_at']);
            $table->index(['receiver_id', 'read_at']);
            $table->index('created_at');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('earnings', function (Blueprint $table) {
            $table->index('paid_at');
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->index('average_rating');
        });

        Schema::table('worker_documents', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['client_id', 'status']);
            $table->dropIndex(['worker_id', 'status']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['booking_id', 'created_at']);
            $table->dropIndex(['receiver_id', 'read_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('earnings', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropIndex(['average_rating']);
        });

        Schema::table('worker_documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
