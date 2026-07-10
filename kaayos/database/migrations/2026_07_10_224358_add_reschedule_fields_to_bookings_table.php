<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('reschedule_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('reschedule_proposed_at')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->string('reschedule_status', 20)->nullable();
            $table->datetime('reschedule_responded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['reschedule_requested_by', 'reschedule_proposed_at', 'reschedule_reason', 'reschedule_status', 'reschedule_responded_at']);
        });
    }
};
