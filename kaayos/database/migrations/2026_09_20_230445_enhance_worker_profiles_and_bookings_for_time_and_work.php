<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->json('tools_equipped')->nullable()->after('skills');
            $table->boolean('tesda_certified')->default(false)->after('government_id_verified');
            $table->boolean('barangay_clearance_verified')->default(false)->after('tesda_certified');
            $table->unsignedSmallInteger('min_notice_hours')->default(2)->after('preferred_hours');
            $table->boolean('emergency_available')->default(false)->after('min_notice_hours');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('property_type', 50)->default('residential')->after('address');
            $table->string('pricing_type', 20)->default('fixed')->after('price');
            $table->decimal('estimated_duration_hours', 4, 2)->default(2.00)->after('pricing_type');
            $table->string('complexity_level', 30)->default('standard')->after('estimated_duration_hours');
            $table->decimal('complexity_multiplier', 3, 2)->default(1.00)->after('complexity_level');
            $table->decimal('scope_amendment_price', 10, 2)->nullable()->after('complexity_multiplier');
            $table->text('scope_amendment_notes')->nullable()->after('scope_amendment_price');
            $table->string('scope_amendment_status', 30)->nullable()->after('scope_amendment_notes');
            $table->timestamp('scope_amendment_requested_at')->nullable()->after('scope_amendment_status');
            $table->string('team_status', 30)->default('none')->after('scope_amendment_requested_at');
            $table->timestamp('team_suggested_at')->nullable()->after('team_status');
            $table->text('team_justification')->nullable()->after('team_suggested_at');
            $table->timestamp('work_started_at')->nullable()->after('team_justification');
            $table->timestamp('work_ended_at')->nullable()->after('work_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'tools_equipped',
                'tesda_certified',
                'barangay_clearance_verified',
                'min_notice_hours',
                'emergency_available',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'property_type',
                'pricing_type',
                'estimated_duration_hours',
                'complexity_level',
                'complexity_multiplier',
                'scope_amendment_price',
                'scope_amendment_notes',
                'scope_amendment_status',
                'scope_amendment_requested_at',
                'team_status',
                'team_suggested_at',
                'team_justification',
                'work_started_at',
                'work_ended_at',
            ]);
        });
    }
};
