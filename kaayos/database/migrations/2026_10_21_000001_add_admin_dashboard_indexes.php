<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('cancelled_at');
            $table->index('declined_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('worker_documents', function (Blueprint $table) {
            $table->index(['status', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['cancelled_at']);
            $table->dropIndex(['declined_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('worker_documents', function (Blueprint $table) {
            $table->dropIndex(['status', 'user_id']);
        });
    }
};
