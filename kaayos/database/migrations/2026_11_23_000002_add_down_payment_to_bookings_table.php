<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('down_payment_amount', 10, 2)->nullable()->after('payment_status');
            $table->dateTime('down_payment_paid_at')->nullable()->after('down_payment_amount');
            $table->decimal('balance_remaining', 10, 2)->nullable()->after('down_payment_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['down_payment_amount', 'down_payment_paid_at', 'balance_remaining']);
        });
    }
};