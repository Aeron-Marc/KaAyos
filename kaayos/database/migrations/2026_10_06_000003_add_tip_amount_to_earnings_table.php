<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = $this->targetTable();

        if ($table === null || Schema::hasColumn($table, 'tip_amount')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->decimal('tip_amount', 8, 2)->default(0);
        });
    }

    public function down(): void
    {
        $table = $this->targetTable();

        if ($table === null || !Schema::hasColumn($table, 'tip_amount')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('tip_amount');
        });
    }

    /**
     * The earnings table may have been renamed to booking_quotes by a
     * later-ordered migration on fresh installs, so check both names.
     */
    private function targetTable(): ?string
    {
        if (Schema::hasTable('earnings')) {
            return 'earnings';
        }

        return Schema::hasTable('booking_quotes') ? 'booking_quotes' : null;
    }
};
