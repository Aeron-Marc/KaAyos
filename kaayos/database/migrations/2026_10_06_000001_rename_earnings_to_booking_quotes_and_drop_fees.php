<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Renames earnings table to booking_quotes and removes platform fee / net calculation.
     */
    public function up(): void
    {
        if (Schema::hasTable('earnings') && !Schema::hasTable('booking_quotes')) {
            Schema::rename('earnings', 'booking_quotes');
        }

        if (Schema::hasTable('booking_quotes')) {
            Schema::table('booking_quotes', function (Blueprint $table) {
                if (Schema::hasColumn('booking_quotes', 'gross_amount') && !Schema::hasColumn('booking_quotes', 'total_amount')) {
                    $table->renameColumn('gross_amount', 'total_amount');
                }
                
                $columnsToDrop = [];
                if (Schema::hasColumn('booking_quotes', 'platform_fee')) {
                    $columnsToDrop[] = 'platform_fee';
                }
                if (Schema::hasColumn('booking_quotes', 'net_amount')) {
                    $columnsToDrop[] = 'net_amount';
                }
                
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_quotes')) {
            Schema::table('booking_quotes', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_quotes', 'platform_fee')) {
                    $table->decimal('platform_fee', 10, 2)->default(0)->after('total_amount');
                }
                if (!Schema::hasColumn('booking_quotes', 'net_amount')) {
                    $table->decimal('net_amount', 10, 2)->default(0)->after('platform_fee');
                }
                if (Schema::hasColumn('booking_quotes', 'total_amount')) {
                    $table->renameColumn('total_amount', 'gross_amount');
                }
            });

            Schema::rename('booking_quotes', 'earnings');
        }
    }
};
