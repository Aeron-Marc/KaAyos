<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('service_category');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('completed_at');
            $table->index('service_category');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['service_category']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['service_category']);
        });
    }
};
