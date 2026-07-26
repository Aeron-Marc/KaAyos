<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->string('type', 30)->default('booking_dispute')->after('id');
            $table->foreignId('reported_worker_id')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign(['reported_worker_id']);
            $table->dropColumn(['type', 'reported_worker_id']);
            $table->foreignId('booking_id')->nullable(false)->change();
        });
    }
};
