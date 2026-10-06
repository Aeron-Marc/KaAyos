<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('issue_category_id')->nullable()->after('service_category');
            $table->string('urgency', 20)->default('normal')->after('complexity_multiplier');
            $table->decimal('urgency_multiplier', 3, 2)->default(1.00)->after('urgency');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['issue_category_id', 'urgency', 'urgency_multiplier']);
        });
    }
};
